FROM python:3

RUN apt-get update && apt-get upgrade -y

WORKDIR /usr/src/app

COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

ENV FLASK_APP=app.py
RUN flask db migrate
RUN flask db upgrade

CMD flask run
