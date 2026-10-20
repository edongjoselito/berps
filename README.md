# berps

## Progressive Web App

BERPS can be installed from a supported browser using the app's install prompt or the browser menu. Production deployments must use HTTPS; `localhost` is treated as a secure context for development.

After a user has visited the pages while online, the service worker can provide read-only offline access to that user's dashboard, My Tasks, Calendar data for previously loaded date ranges, and published Knowledge Base articles. Private cached pages are separated by account and cleared on logout or account change. Forms and other changes are not queued offline, and other uncached pages show the offline fallback.