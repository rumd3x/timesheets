<p align="center">
    <img src="https://imgplaceholder.com/400x80/transparent?text=Timesheets&font-size=60&font-family=Quicksand_Bold" alt="Timesheets">
</p>
<p align="center">
    Automated timesheet generation and timestamp tracking.
</p>


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

- Adicionar Resumo de Horas Totais na (Visao Mensal)

- Adicionar form de insert de timestamp na Visao Mensal

- Job Diario de Sanitizacao (Verificar existencia de duas ou mais entradas ou saidas consecutivas [usando timestamp ORDER BY moment e ver se a flag entry eh igual], e manter apenas uma).

- Job Diario de Sanitizacao 2 (Verificar existencia de entrada sem saida e saida sem entrada e trata-las)

- Job para Planilha de Horas Target
