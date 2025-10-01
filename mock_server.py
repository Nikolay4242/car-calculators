from flask import Flask, jsonify, request
import os

app = Flask(__name__)


# Если MOCK = False и указан VPS_API_URL, можно пробрасывать запросы на реальный сервер
MOCK = True
VPS_API_URL = os.getenv("VPS_API_URL", None)

@app.route("/parse/mobilede", methods=["POST"])
def parse_mobilede():
    # В режиме MOCK возвращаем пример JSON
    # В боевом режиме запрос можно было бы отправить на реальный сервер (VPS_API_URL)
    if MOCK or VPS_API_URL is None:
        return jsonify({
            "site": "mobile.de",
            "status": "success",
            "data": {
                "title": "BMW 320d 2020",
                "price": "25 000 EUR",
                "mileage": "45 000 km",
                "engine": "1995 cm³",
                "fuel": "Diesel"
            }
        })
    else:
        # Здесь можно реализовать проброс запроса на реальный API
        return jsonify({"error": "Forwarding not implemented"}), 501


@app.route("/parse/carscom", methods=["POST"])
def parse_carscom():
    # Аналогично mobile.de — в MOCK-режиме отдаем тестовые данные
    if MOCK or VPS_API_URL is None:
        return jsonify({
            "site": "cars.com",
            "status": "success",
            "data": {
                "title": "Ford F-150 2022",
                "price": "$44 000",
                "mileage": "12 000 mi",
                "engine": "3.5L V6",
                "fuel": "Hybrid"
            }
        })
    else:
        return jsonify({"error": "Forwarding not implemented"}), 501


@app.route("/parse/encar", methods=["POST"])
def parse_encar():
    # Для Encar (Корея) тоже выдаем демо-данные
    if MOCK or VPS_API_URL is None:
        return jsonify({
            "site": "encar.com",
            "status": "success",
            "data": {
                "title": "Hyundai Sonata 2021",
                "price": "₩22 000 000",
                "mileage": "30 000 km",
                "engine": "2000 cm³",
                "fuel": "Petrol"
            }
        })
    else:
        return jsonify({"error": "Forwarding not implemented"}), 501


@app.route("/parse/dongchedi", methods=["POST"])
def parse_dongchedi():
    # Для Dongchedi (Китай) — тестовые данные
    if MOCK or VPS_API_URL is None:
        return jsonify({
            "site": "dongchedi.com",
            "status": "success",
            "data": {
                "title": "BYD Han EV 2023",
                "price": "¥230 000",
                "mileage": "5 000 km",
                "engine": "EV",
                "fuel": "Electric"
            }
        })
    else:
        return jsonify({"error": "Forwarding not implemented"}), 501


if __name__ == "__main__":
    # Запускаем Flask-сервер на локальном порту 5000
    # В режиме разработки используем debug=True
    app.run(host="0.0.0.0", port=5000, debug=True)
