#!/bin/bash

# Define variables
LARAVEL_DIR="/path/to/your/laravel/project"
HOST="0.0.0.0"
PORT="8000"
QUEUE_TIMEOUT=120

# Start the Laravel application
nohup php $LARAVEL_DIR/artisan serve --host=$HOST --port=$PORT > $LARAVEL_DIR/serve.log 2>&1 &

echo "Laravel application started on http://$HOST:$PORT"

# Start the Laravel queue worker
nohup php $LARAVEL_DIR/artisan queue:work --timeout=$QUEUE_TIMEOUT > $LARAVEL_DIR/queue.log 2>&1 &

echo "Laravel queue worker started with a timeout of $QUEUE_TIMEOUT seconds"
