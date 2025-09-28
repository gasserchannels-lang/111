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
        const previousState = { ...this.state };
        this.state = { ...this.state, ...newState };

        // Run middleware
        this.runMiddleware(previousState, this.state);

        // Notify listeners
        this.notifyListeners(previousState, this.state);
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
    runMiddleware(previousState, newState) {
        for (const middleware of this.middleware) {
            try {
                middleware(previousState, newState, this);
            } catch {
                // console.error('Middleware error:', error);
            }
        }
    }

    /**
     * Notify listeners
     */
    notifyListeners(previousState, newState) {
        for (const [key, callbacks] of this.listeners.entries()) {
            const oldValue = this.getNestedValue(previousState, key);
            const newValue = this.getNestedValue(newState, key);

            if (oldValue !== newValue) {
                for (const callback of callbacks) {
                    try {
                        callback(newValue, oldValue, newState);
                    } catch {
                        // console.error('Listener error:', error);
                    }
                }
            }
        }
    }

    /**
     * Get nested value from object
     */
    getNestedValue(object, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined
                ? current[key]
                : undefined;
        }, object);
    }

    /**
     * Set nested value in object
     */
    setNestedValue(object, path, value) {
        const keys = path.split('.');
        const lastKey = keys.pop();
        const target = keys.reduce((current, key) => {
            if (!current[key] || typeof current[key] !== 'object') {
                current[key] = {};
            }
            return current[key];
        }, object);
        target[lastKey] = value;
    }

    /**
     * Cache data
     */
    cacheData(key, data, ttl) {
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
        if (!cached) return;

        const isExpired = Date.now() - cached.timestamp > cached.ttl;
        if (isExpired) {
            this.cache.delete(key);
            return;
        }

        return cached.data;
    }

    /**
     * Clear cache
     */
    clearCache(key) {
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
            return data ? JSON.parse(data) : undefined;
        } catch {
            // console.error('Failed to restore state:', error);
            return;
        }
    }

    /**
     * Clear persisted state
     */
    clearPersisted(key) {
        if (key) {
            localStorage.removeItem(`store_${key}`);
        } else {
            // Clear all store keys
            for (const k of Object.keys(localStorage)) {
                if (k.startsWith('store_')) {
                    localStorage.removeItem(k);
                }
            }
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
store.addMiddleware((previousState, newState) => {
    // Check for error states
    for (const key of Object.keys(newState)) {
        if (key.endsWith('Error') && newState[key]) {
            // console.error(`Error in ${key}:`, newState[key]);
        }
    }
});

// Export store
export default store;
