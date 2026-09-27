FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 PYTHONUNBUFFERED=1
WORKDIR /app
COPY requirements.txt worker.py init_solr.py ./
RUN pip install --no-cache-dir -r requirements.txt && useradd --system --uid 10001 askmail && mkdir /data && chown askmail:askmail /data
USER askmail
VOLUME /data
CMD ["python", "worker.py"]
