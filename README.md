<p align="center">
    <img src="https://imgplaceholder.com/400x80/transparent?text=Timesheets&font-size=60&font-family=Quicksand_Bold" alt="Timesheets">
</p>
<p align="center">
    Automated timesheet generation and timestamp tracking.
</p>

![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/edmur/timesheets.svg)
![Docker Cloud Automated build](https://img.shields.io/docker/cloud/automated/edmur/timesheets.svg)
![License](https://img.shields.io/github/license/rumd3x/timesheets.svg)

# About

This project provides a simple and clean looking web interface to help you manage and better track your work time.
<br>
Also provides an IFTTT compatible API so that your timestamp markings can be fully automated.
<br>
It is also fully configurable to suit your needs, that includes the upload of a custom Spreadsheet to be used as template, that will be automatically filled and sent to the configured email addresses by the end of the month.

## Usage

Start the docker container with the commands below:
```
docker volume create timesheets-storage

docker run -d --name timesheets --restart always \
-p 80:80 -v timesheets-storage:/var/www/html/storage \
edmur/timesheets
```

Now navigate to http://yourhost:port/ and login.

The default user e-mail is `admin@admin.com` and password is `changethis`.

Make sure to configure the settings in "Settings" section and change email and password on "My Account" section.

- Environment Variables:

```
(Mandatory)
MAIL_HOST=smtp.mailserver.com
MAIL_PORT=465
MAIL_FROM_ADDRESS=example@mysite.com
MAIL_FROM_NAME='Timesheet Bot'
MAIL_USERNAME=example@mysite.com
MAIL_PASSWORD=myemailpassword
MAIL_ENCRYPTION=ssl/tls/empty

(Optional)
IFTTT_EVENT=my_ifttt_webhook_event
IFTTT_KEY=my-ifttt-webhook-key-here
TZ=America/Sao_Paulo
```

## API Documentation

- Todo

## To Do

- Job para Planilha de Horas Target

- Template HTML pro corpo do Email de envio das planilhas

- Organizar as Logicas de `User` e `AppSetting` em repositories

- Documentacao API

- Adicionar possibilidade de gerar nova API Key
