import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js'
      ],
      refresh: true,
    }),
  ],

  // Build optimization
  build: {
    // Enable source maps for debugging
    sourcemap: true,

    // Minification settings
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
        pure_funcs: ['console.log', 'console.info', 'console.debug']
      },
      mangle: {
        safari10: true
      }
    },

    // Asset handling
    assetsInlineLimit: 4096,

    // Chunk size warnings
    chunkSizeWarningLimit: 1000
  },

  // Development server
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost'
    }
  },

  // CSS handling
  css: {
    devSourcemap: true
  },

  // Resolve configuration
  resolve: {
    alias: {
      '@': '/resources/js',
      '~': '/resources'
    }
  }
});
