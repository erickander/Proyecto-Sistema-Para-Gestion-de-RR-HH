# Microservicio IA Analyzer

Servicio HTTP local para analizar CV + test de postulantes con Groq.

## Endpoints

- `GET /health`: verifica que el servicio este activo.
- `POST /analyze`: recibe `vacante`, `cv_text` y `test`, llama a Groq y devuelve el JSON de analisis.

## Ejecucion

Desde la raiz del proyecto:

```bash
npm run microservice:ia
```

Por defecto escucha en:

```text
http://127.0.0.1:4101
```

El servicio carga variables desde `.env` del proyecto y opcionalmente desde `microservices/ai-analyzer/.env`.
