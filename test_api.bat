@echo off
set TOKEN=70^|zEXTpGVbFr60yuqo1SpqAQcKKKF9MX8GBTvVsyO66bd55a17
curl -X GET "http://localhost:8000/api/debug-headers" -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" -H "Accept: application/json"