#!/bin/bash

# Define variables
# LARAVEL_DIR="/path/to/your/laravel/project"
# HOST="0.0.0.0"
# PORT="8000"
QUEUE_TIMEOUT=120

# Start the Laravel application
nohup php artisan serve --host=0.0.0.0 --port=8000 > serve.log 2>&1 &

echo "Laravel application started on http://$HOST:$PORT"

# Start the Laravel queue worker
nohup php artisan queue:work --timeout=120 > queue.log 2>&1 &

echo "Laravel queue worker started with a timeout of $QUEUE_TIMEOUT seconds"
