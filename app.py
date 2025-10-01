from flask import Flask, request, jsonify
from werkzeug.exceptions import HTTPException
import re

# mock-парсеры
from encar_parser import parse_encar
from dongchedi_parser import parse_dongchedi

app = Flask(__name__)

def detect_source(url: str) -> str:
    u = url.lower().strip()
    if "encar.com" in u:
        return "encar"
    if "dongchedi.com" in u or "dcdapp.com" in u:
        return "dongchedi"
    if re.search(r'\bcars\.com\b', u):
        return "cars"
    return "unknown"

@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})

@app.route("/version", methods=["GET"])
def version():
    return jsonify({"service": "car-parser-api-mock", "v": "1.0.0"})

@app.errorhandler(Exception)
def handle_exception(e):
    if isinstance(e, HTTPException):
        return jsonify({"error": e.description}), e.code
    return jsonify({"error": str(e)}), 500

@app.route("/parse", methods=["GET", "POST"])
def parse():
    link = None
    if request.method == "POST":
        if request.is_json:
            data = request.get_json(silent=True) or {}
            link = (data.get("link") or "").strip()
        else:
            link = (request.form.get("link") or "").strip()
    else:
        link = (request.args.get("link") or "").strip()

    if not link:
        return jsonify({"error": "link is required"}), 400

    src = detect_source(link)
    try:
        if src == "encar":
            data = parse_encar(link)
        elif src == "dongchedi":
            data = parse_dongchedi(link)
        elif src == "cars":
            return jsonify({"error": "cars.com parser is not enabled in mock"}), 501
        else:
            return jsonify({"error": "unsupported source"}), 400

        return jsonify(data)

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(debug=True)
