/**
 * Error Tracking Utility
 * Provides comprehensive error tracking and reporting
 */
class ErrorTracker {
    constructor() {
        this.errors = [];
        this.maxErrors = 100;
        this.reportEndpoint = '/api/errors/report';
        this.isEnabled = true;
        this.rateLimit = {
            max: 10,
            window: 60000, // 1 minute
            requests: []
        };
        
        this.init();
    }

    /**
     * Initialize error tracking
     */
    init() {
        // Global error handler
        window.addEventListener('error', (event) => {
            this.trackError({
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                url: window.location.href
            });
        });

        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            this.trackError({
                type: 'promise',
                message: event.reason?.message || 'Unhandled Promise Rejection',
                stack: event.reason?.stack,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                url: window.location.href
            });
        });

        // Resource loading error handler
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.trackError({
                    type: 'resource',
                    message: `Failed to load resource: ${event.target.src || event.target.href}`,
                    element: event.target.tagName,
                    src: event.target.src || event.target.href,
                    timestamp: new Date().toISOString(),
                    userAgent: navigator.userAgent,
                    url: window.location.href
                });
            }
        }, true);
    }

    /**
     * Track an error
     */
    trackError(error) {
        if (!this.isEnabled) return;

        // Add unique ID
        error.id = this.generateId();
        
        // Add session info
        error.sessionId = this.getSessionId();
        error.userId = this.getUserId();
        
        // Add browser info
        error.browser = this.getBrowserInfo();
        error.screen = this.getScreenInfo();
        
        // Add performance info
        error.performance = this.getPerformanceInfo();
        
        // Store error
        this.errors.push(error);
        
        // Keep only recent errors
        if (this.errors.length > this.maxErrors) {
            this.errors = this.errors.slice(-this.maxErrors);
        }
        
        // Report error if rate limit allows
        if (this.canReport()) {
            this.reportError(error);
        }
        
        // Log to console in development
        if (process.env.NODE_ENV === 'development') {
            // console.error('Error tracked:', error);
        }
    }

    /**
     * Report error to server
     */
    async reportError(error) {
        try {
            const response = await fetch(this.reportEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(error)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            // Update rate limit
            this.rateLimit.requests.push(Date.now());
            
        } catch {
            // console.error('Failed to report error:', err);
        }
    }

    /**
     * Check if can report (rate limiting)
     */
    canReport() {
        const now = Date.now();
        const windowStart = now - this.rateLimit.window;
        
        // Remove old requests
        this.rateLimit.requests = this.rateLimit.requests.filter(
            time => time > windowStart
        );
        
        return this.rateLimit.requests.length < this.rateLimit.max;
    }

    /**
     * Generate unique ID
     */
    generateId() {
        return Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
    }

    /**
     * Get session ID
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem('error_tracker_session');
        if (!sessionId) {
            sessionId = this.generateId();
            sessionStorage.setItem('error_tracker_session', sessionId);
        }
        return sessionId;
    }

    /**
     * Get user ID
     */
    getUserId() {
        // This would be set by your authentication system
        return window.userId || null;
    }

    /**
     * Get browser info
     */
    getBrowserInfo() {
        const ua = navigator.userAgent;
        const browsers = {
            chrome: /Chrome\/(\d+)/,
            firefox: /Firefox\/(\d+)/,
            safari: /Safari\/(\d+)/,
            edge: /Edg\/(\d+)/,
            ie: /MSIE (\d+)/
        };
        
        for (const [name, regex] of Object.entries(browsers)) {
            const match = ua.match(regex);
            if (match) {
                return { name, version: match[1] };
            }
        }
        
        return { name: 'unknown', version: 'unknown' };
    }

    /**
     * Get screen info
     */
    getScreenInfo() {
        return {
            width: screen.width,
            height: screen.height,
            availWidth: screen.availWidth,
            availHeight: screen.availHeight,
            colorDepth: screen.colorDepth,
            pixelDepth: screen.pixelDepth
        };
    }

    /**
     * Get performance info
     */
    getPerformanceInfo() {
        if (!window.performance) return null;
        
        const navigation = performance.getEntriesByType('navigation')[0];
        const memory = performance.memory;
        
        return {
            loadTime: navigation ? navigation.loadEventEnd - navigation.loadEventStart : null,
            domContentLoaded: navigation ? navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart : null,
            memory: memory ? {
                used: memory.usedJSHeapSize,
                total: memory.totalJSHeapSize,
                limit: memory.jsHeapSizeLimit
            } : null
        };
    }

    /**
     * Get all errors
     */
    getErrors() {
        return [...this.errors];
    }

    /**
     * Get errors by type
     */
    getErrorsByType(type) {
        return this.errors.filter(error => error.type === type);
    }

    /**
     * Get recent errors
     */
    getRecentErrors(minutes = 60) {
        const cutoff = Date.now() - (minutes * 60 * 1000);
        return this.errors.filter(error => 
            new Date(error.timestamp).getTime() > cutoff
        );
    }

    /**
     * Clear errors
     */
    clearErrors() {
        this.errors = [];
    }

    /**
     * Enable/disable tracking
     */
    setEnabled(enabled) {
        this.isEnabled = enabled;
    }

    /**
     * Get error statistics
     */
    getStats() {
        const types = {};
        this.errors.forEach(error => {
            types[error.type] = (types[error.type] || 0) + 1;
        });
        
        return {
            total: this.errors.length,
            types,
            recent: this.getRecentErrors(60).length,
            rateLimit: {
                used: this.rateLimit.requests.length,
                max: this.rateLimit.max,
                window: this.rateLimit.window
            }
        };
    }

    /**
     * Export errors as JSON
     */
    exportErrors() {
        return JSON.stringify(this.errors, null, 2);
    }

    /**
     * Import errors from JSON
     */
    importErrors(json) {
        try {
            const errors = JSON.parse(json);
            if (Array.isArray(errors)) {
                this.errors = errors;
                return true;
            }
        } catch {
            // console.error('Failed to import errors:', err);
        }
        return false;
    }
}

// Create global error tracker instance
const errorTracker = new ErrorTracker();

// Export for use in modules
export default errorTracker;

// Also make available globally for debugging
window.errorTracker = errorTracker;
