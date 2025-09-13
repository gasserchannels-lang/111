/**
 * Simple State Management Store
 * Provides centralized state management for the application
 */
class Store {
    constructor() {
        this.state = {};
        this.listeners = new Map();
        this.middleware = [];
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    }

    /**
     * Get current state
     */
    getState() {
        return { ...this.state };
    }

    /**
     * Set state
     */
    setState(newState) {
        const prevState = { ...this.state };
        this.state = { ...this.state, ...newState };

        // Run middleware
        this.runMiddleware(prevState, this.state);

        // Notify listeners
        this.notifyListeners(prevState, this.state);
    }

    /**
     * Subscribe to state changes
     */
    subscribe(key, callback) {
        if (!this.listeners.has(key)) {
            this.listeners.set(key, new Set());
        }
        this.listeners.get(key).add(callback);

        // Return unsubscribe function
        return () => {
            const listeners = this.listeners.get(key);
            if (listeners) {
                listeners.delete(callback);
                if (listeners.size === 0) {
                    this.listeners.delete(key);
                }
            }
        };
    }

    /**
     * Unsubscribe from state changes
     */
    unsubscribe(key, callback) {
        const listeners = this.listeners.get(key);
        if (listeners) {
            listeners.delete(callback);
            if (listeners.size === 0) {
                this.listeners.delete(key);
            }
        }
    }

    /**
     * Add middleware
     */
    addMiddleware(middleware) {
        this.middleware.push(middleware);
    }

    /**
     * Run middleware
     */
    runMiddleware(prevState, newState) {
        this.middleware.forEach(middleware => {
            try {
                middleware(prevState, newState, this);
            } catch {
                // console.error('Middleware error:', error);
            }
        });
    }

    /**
     * Notify listeners
     */
    notifyListeners(prevState, newState) {
        this.listeners.forEach((callbacks, key) => {
            const oldValue = this.getNestedValue(prevState, key);
            const newValue = this.getNestedValue(newState, key);

            if (oldValue !== newValue) {
                callbacks.forEach(callback => {
                    try {
                        callback(newValue, oldValue, newState);
                    } catch {
                        // console.error('Listener error:', error);
                    }
                });
            }
        });
    }

    /**
     * Get nested value from object
     */
    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined
                ? current[key]
                : undefined;
        }, obj);
    }

    /**
     * Set nested value in object
     */
    setNestedValue(obj, path, value) {
        const keys = path.split('.');
        const lastKey = keys.pop();
        const target = keys.reduce((current, key) => {
            if (!current[key] || typeof current[key] !== 'object') {
                current[key] = {};
            }
            return current[key];
        }, obj);
        target[lastKey] = value;
    }

    /**
     * Cache data
     */
    cacheData(key, data, ttl = null) {
        const timeout = ttl || this.cacheTimeout;
        this.cache.set(key, {
            data,
            timestamp: Date.now(),
            ttl: timeout,
        });
    }

    /**
     * Get cached data
     */
    getCachedData(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;

        const isExpired = Date.now() - cached.timestamp > cached.ttl;
        if (isExpired) {
            this.cache.delete(key);
            return null;
        }

        return cached.data;
    }

    /**
     * Clear cache
     */
    clearCache(key = null) {
        if (key) {
            this.cache.delete(key);
        } else {
            this.cache.clear();
        }
    }

    /**
     * Persist state to localStorage
     */
    persist(key, data) {
        try {
            localStorage.setItem(`store_${key}`, JSON.stringify(data));
        } catch {
            // console.error('Failed to persist state:', error);
        }
    }

    /**
     * Restore state from localStorage
     */
    restore(key) {
        try {
            const data = localStorage.getItem(`store_${key}`);
            return data ? JSON.parse(data) : null;
        } catch {
            // console.error('Failed to restore state:', error);
            return null;
        }
    }

    /**
     * Clear persisted state
     */
    clearPersisted(key = null) {
        if (key) {
            localStorage.removeItem(`store_${key}`);
        } else {
            // Clear all store keys
            Object.keys(localStorage).forEach(k => {
                if (k.startsWith('store_')) {
                    localStorage.removeItem(k);
                }
            });
        }
    }

    /**
     * Reset store
     */
    reset() {
        this.state = {};
        this.listeners.clear();
        this.cache.clear();
    }

    /**
     * Get store statistics
     */
    getStats() {
        return {
            stateKeys: Object.keys(this.state).length,
            listeners: this.listeners.size,
            cacheSize: this.cache.size,
            middleware: this.middleware.length,
        };
    }
}

// Create global store instance
const store = new Store();

// Add logging middleware
store.addMiddleware(() => {
    if (process.env.NODE_ENV === 'development') {
        // Debug logging (disabled in production)
        // Log state changes for debugging
        //     prev: prevState,
        //     new: newState,
        //     stats: store.getStats()
        // });
    }
});

// Add error handling middleware
store.addMiddleware((prevState, newState) => {
    // Check for error states
    Object.keys(newState).forEach(key => {
        if (key.endsWith('Error') && newState[key]) {
            // console.error(`Error in ${key}:`, newState[key]);
        }
    });
});

// Export store
export default store;
