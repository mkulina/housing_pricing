# Housing Price Predictor

A web app that predicts house prices based on square footage and bedrooms. Built with Laravel and a linear regression model in Python.

## Quick Start

```bash
docker compose up --build
```

Then open http://localhost:8080

## Features

- **Price Prediction**: Enter house details, get an instant price estimate
- **Dark Mode**: Toggle saves your preference to cookies
- **Prediction History**: Collapsible panel with relative timestamps
- **Delete Predictions**: Remove any prediction from history
- **Form Validation**: Real-time feedback as you type
- **Animated Results**: Price counts up when displayed
- **Rate Limiting**: 30 predictions per minute to prevent abuse
- **Keyboard Shortcuts**: Press `Esc` to clear the form
- **Mobile Friendly**: Works on any screen size

## Project Structure

```
app/
  Controllers/PredictionController.php  - handles API requests
  Services/PredictionService.php        - prediction logic
  Models/Prediction.php                 - database model

ml/
  data/training_data.csv                - training dataset
  src/train.py                          - trains the model
  src/predict.py                        - makes predictions
  models/housing_model.pkl              - trained model file

routes/
  web.php                               - serves the homepage
  api.php                               - API endpoints with rate limiting

public/
  css/app.css                           - styles
  js/app.js                             - frontend logic

resources/views/index.blade.php         - main page template
tests/Feature/PredictionApiTest.php     - API tests
```

## API Endpoints

**POST /api/predict** (rate limited: 30/min)
```json
{ "square_footage": 1500, "bedrooms": 3 }
```

**GET /api/history**

Returns past predictions.

**DELETE /api/predictions/{id}**

Removes a prediction.

## Running Tests

```bash
php artisan test
```

12 tests covering validation, predictions, history, deletion, and rate limiting.

## Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
pip3 install -r requirements.txt
python3 ml/src/train.py
php artisan serve
```
