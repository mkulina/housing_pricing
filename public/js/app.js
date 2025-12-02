/**
 * Housing Price Predictor - Main Application JavaScript
 */

// Cookie helper functions
const CookieManager = {
    set(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    },

    get(name) {
        const nameEQ = `${name}=`;
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            cookie = cookie.trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return null;
    }
};

// Format numbers with commas
const formatNumber = (num) => {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

// Parse formatted number back to integer
const parseFormattedNumber = (str) => {
    return parseInt(str.replace(/,/g, ''), 10) || 0;
};

// Relative time formatting
const getRelativeTime = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSecs < 60) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString();
};

// Animate counting up to a number
const animateValue = (element, start, end, duration) => {
    const startTime = performance.now();
    
    const update = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = start + (end - start) * easeOut;
        
        element.textContent = '$' + formatNumber(Math.floor(current));
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            // Ensure final value is exact
            element.textContent = '$' + formatNumber(end);
        }
    };
    
    requestAnimationFrame(update);
};

// Theme Manager
const ThemeManager = {
    init() {
        this.toggle = document.getElementById('themeToggle');
        this.body = document.body;
        
        if (!this.toggle) return;

        // Load saved theme
        const savedTheme = CookieManager.get('theme') || 'light';
        if (savedTheme === 'dark') {
            this.body.classList.add('dark-mode');
            this.toggle.checked = true;
        }

        // Listen for changes
        this.toggle.addEventListener('change', () => this.handleToggle());
    },

    handleToggle() {
        this.body.classList.toggle('dark-mode');
        const isDark = this.body.classList.contains('dark-mode');
        CookieManager.set('theme', isDark ? 'dark' : 'light', 365);
    }
};

// Input Formatter - formats square footage with commas
const InputFormatter = {
    init() {
        this.sqftInput = document.getElementById('square_footage');
        this.bedroomsInput = document.getElementById('bedrooms');
        
        if (!this.sqftInput) return;

        // Format on input
        this.sqftInput.addEventListener('input', (e) => this.handleInput(e));
        this.sqftInput.addEventListener('blur', (e) => this.handleBlur(e));
        
        // Validation feedback
        this.sqftInput.addEventListener('input', () => this.validateField(this.sqftInput, 100, 10000));
        this.bedroomsInput.addEventListener('input', () => this.validateField(this.bedroomsInput, 1, 10));
    },

    handleInput(e) {
        const input = e.target;
        let value = input.value.replace(/,/g, '');
        
        // Only allow digits
        value = value.replace(/\D/g, '');
        
        if (value) {
            input.value = formatNumber(parseInt(value, 10));
        }
    },

    handleBlur(e) {
        const input = e.target;
        const value = parseFormattedNumber(input.value);
        if (value) {
            input.value = formatNumber(value);
        }
    },

    validateField(input, min, max) {
        const value = parseFormattedNumber(input.value);
        const wrapper = input.closest('.form-group');
        
        wrapper.classList.remove('valid', 'invalid');
        
        if (input.value === '') return;
        
        if (value >= min && value <= max) {
            wrapper.classList.add('valid');
        } else {
            wrapper.classList.add('invalid');
        }
    }
};

// Prediction Form Handler
const PredictionForm = {
    init() {
        this.form = document.getElementById('predictionForm');
        this.submitBtn = document.getElementById('submitBtn');
        this.result = document.getElementById('result');
        this.errorMessage = document.getElementById('errorMessage');
        this.predictedPrice = document.getElementById('predictedPrice');
        this.skeleton = document.getElementById('resultSkeleton');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!this.form) return;

        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
    },

    handleKeyboard(e) {
        // Escape to clear form
        if (e.key === 'Escape') {
            this.form.reset();
            this.hideMessages();
            document.querySelectorAll('.form-group').forEach(g => {
                g.classList.remove('valid', 'invalid');
            });
        }
    },

    async handleSubmit(e) {
        e.preventDefault();
        
        this.hideMessages();
        this.setLoading(true);

        const formData = this.getFormData();

        try {
            const response = await this.sendPredictionRequest(formData);
            const data = await response.json();

            if (response.ok && data.success) {
                this.showResult(data.predicted_price);
            } else {
                throw new Error(data.message || 'Prediction failed');
            }
        } catch (error) {
            this.showError(error.message);
        } finally {
            this.setLoading(false);
        }
    },

    getFormData() {
        return {
            square_footage: parseFormattedNumber(document.getElementById('square_footage').value),
            bedrooms: parseInt(document.getElementById('bedrooms').value)
        };
    },

    async sendPredictionRequest(data) {
        return fetch('/api/predict', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify(data)
        });
    },

    hideMessages() {
        this.result.classList.remove('show');
        this.errorMessage.classList.remove('show');
        if (this.skeleton) this.skeleton.classList.remove('show');
    },

    setLoading(isLoading) {
        this.submitBtn.disabled = isLoading;
        
        if (isLoading) {
            this.submitBtn.innerHTML = '<span class="loading"></span>Predicting...';
            // Show skeleton
            if (this.skeleton) {
                this.skeleton.classList.add('show');
            }
        } else {
            this.submitBtn.innerHTML = 'Predict Price';
            if (this.skeleton) {
                this.skeleton.classList.remove('show');
            }
        }
    },

    showResult(price) {
        // Hide skeleton first
        if (this.skeleton) {
            this.skeleton.classList.remove('show');
        }
        
        // Parse the price (remove commas and convert to number)
        const numericPrice = parseFloat(price.replace(/,/g, ''));
        
        // Show result container
        this.result.classList.add('show');
        
        // Animate the price counting up
        animateValue(this.predictedPrice, 0, numericPrice, 800);
        
        // Refresh history after new prediction
        HistoryManager.loadHistory();
    },

    showError(message) {
        this.errorMessage.textContent = `Error: ${message}`;
        this.errorMessage.classList.add('show');
    }
};

// History Manager
const HistoryManager = {
    init() {
        this.card = document.getElementById('historySection');
        this.toggle = document.getElementById('historyToggle');
        this.table = document.getElementById('historyTable');
        this.body = document.getElementById('historyBody');
        this.loading = document.getElementById('historyLoading');
        this.empty = document.getElementById('historyEmpty');
        this.loaded = false;

        if (!this.toggle) return;

        this.toggle.addEventListener('click', () => this.handleToggle());
    },

    handleToggle() {
        const isExpanded = this.card.classList.toggle('expanded');
        if (isExpanded && !this.loaded) {
            this.loadHistory();
        }
    },

    async loadHistory() {
        this.loading.style.display = 'block';
        this.table.style.display = 'none';
        this.empty.style.display = 'none';

        try {
            const response = await fetch('/api/history');
            const data = await response.json();

            if (data.success && data.predictions.length > 0) {
                this.renderHistory(data.predictions);
                this.table.style.display = 'table';
            } else {
                this.empty.style.display = 'flex';
            }
        } catch (error) {
            this.empty.querySelector('.empty-text').textContent = 'Failed to load history';
            this.empty.style.display = 'flex';
        } finally {
            this.loading.style.display = 'none';
            this.loaded = true;
        }
    },

    renderHistory(predictions) {
        this.body.innerHTML = predictions.map(p => `
            <tr data-id="${p.id}">
                <td>${p.square_footage}</td>
                <td>${p.bedrooms}</td>
                <td>${p.predicted_price}</td>
                <td title="${p.created_at}">${getRelativeTime(p.created_at)}</td>
                <td class="action-cell">
                    <button class="delete-btn" onclick="HistoryManager.deletePrediction(${p.id})" title="Delete">
                        ×
                    </button>
                </td>
            </tr>
        `).join('');
    },

    async deletePrediction(id) {
        if (!confirm('Delete this prediction?')) return;

        try {
            const response = await fetch(`/api/predictions/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                // Remove row with animation
                const row = this.body.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.classList.add('removing');
                    setTimeout(() => {
                        row.remove();
                        // Check if table is now empty
                        if (this.body.children.length === 0) {
                            this.table.style.display = 'none';
                            this.empty.style.display = 'flex';
                        }
                    }, 300);
                }
            }
        } catch (error) {
            console.error('Failed to delete prediction:', error);
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
    InputFormatter.init();
    PredictionForm.init();
    HistoryManager.init();
});
