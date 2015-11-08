GIF frontend
===============

*Backend: https://github.com/Vrenc/gif-backend*

### Configuration
Create a config file named `config/config.php`. See `config/config.example.php` for an example.

### Docker Compose example
```
frontend:
    image: gif-frontend
    ports:
        - "80:80"
backend:
    image: gif-backend
    ports:
        - "3000:3000"
    links:
        - redis
redis:
    image: redis
```