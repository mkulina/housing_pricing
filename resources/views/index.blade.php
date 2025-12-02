<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Housing Price Predictor</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <div class="card">
            {{-- Theme Toggle --}}
            <div class="theme-toggle-wrapper">
                <span class="theme-toggle-label">Dark Mode (nice)</span>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                </label>
            </div>

            {{-- Header --}}
            <h1>Housing Price Predictor</h1>
            <p class="subtitle">Enter house details to get an estimated price</p>

            {{-- Prediction Form --}}
            <form id="predictionForm">
                <div class="form-group">
                    <label for="square_footage">Square Footage</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="square_footage"
                            name="square_footage"
                            placeholder="e.g., 1,500"
                            inputmode="numeric"
                            autocomplete="off"
                            required
                        >
                        <span class="validation-icon valid-icon">✓</span>
                        <span class="validation-icon invalid-icon">✗</span>
                    </div>
                    <div class="input-hint">Between 100 and 10,000 sq ft</div>
                </div>

                <div class="form-group">
                    <label for="bedrooms">Number of Bedrooms</label>
                    <div class="input-wrapper">
                        <input
                            type="number"
                            id="bedrooms"
                            name="bedrooms"
                            placeholder="e.g., 3"
                            min="1"
                            max="10"
                            required
                        >
                        <span class="validation-icon valid-icon">✓</span>
                        <span class="validation-icon invalid-icon">✗</span>
                    </div>
                    <div class="input-hint">Between 1 and 10 bedrooms</div>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    Predict Price
                </button>
            </form>

            {{-- Keyboard Hint --}}
            <div class="keyboard-hint">
                Press <kbd>Esc</kbd> to clear form
            </div>

            {{-- Error Message --}}
            <div class="error" id="errorMessage"></div>

            {{-- Loading Skeleton --}}
            <div class="result-skeleton" id="resultSkeleton">
                <div class="skeleton-label"></div>
                <div class="skeleton-price"></div>
            </div>

            {{-- Result --}}
            <div class="result" id="result">
                <div class="result-label">Estimated Price</div>
                <div class="result-price" id="predictedPrice">$0</div>
            </div>
        </div>

        {{-- History Card --}}
        <div class="card history-card" id="historySection">
            <div class="history-toggle" id="historyToggle">
                <div class="history-header">
                    <span class="history-title">Prediction History</span>
                </div>
                <span class="history-arrow">▼</span>
            </div>
            <div class="history-content">
                <div class="history-loading" id="historyLoading">Loading...</div>
                <table class="history-table" id="historyTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Sq Ft</th>
                            <th>Beds</th>
                            <th>Price</th>
                            <th>When</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="historyBody"></tbody>
                </table>
                <div class="history-empty" id="historyEmpty">
                    <div class="empty-icon">📊</div>
                    <div class="empty-text">No predictions yet.<br>Try making one above!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
