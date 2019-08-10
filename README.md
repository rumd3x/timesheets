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

```bash
docker volume create timesheets-storage

docker run -d --name timesheets --restart always \
-p 80:80 -v timesheets-storage:/var/www/html/storage \
edmur/timesheets
```

Now navigate to http://yourhost:port/ and login.

The default user e-mail is `admin@admin.com` and password is `changethis`.

Make sure to configure the settings in "Settings" section and change email and password on "My Account" section.

- Environment Variables:

```env
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

In addition to using the web interface, you can also use the API to control timestamps.

First, go to "My Account" section, and under **API**, click `Show Key` on **Your API Key**.

The API was designed to be triggered by **IFTTT** Webhook service.

It can guess the Content-Type encoding. Works well with JSON and x-www-form-urlencoded.

### Endpoints

#### `POST` Timestamp IN

Creates a new **Entry** Timestamp.

```bash
{{URL}}/api/timestamp/in
```

##### Body

|  Field  |  Type  |                           Description                          |
| :-----: | :----: | :------------------------------------------------------------: |
| api_key | String | Your API Key.                                                  |
|   ts    | String | The timestamp in IFTTT "moment" format. Smart guess otherwise. |

##### Returns

|  Status Code  |            Message              |                           Reason                               |
| :-----------: | :-----------------------------: | :------------------------------------------------------------: |
|     200       | Timestamp inserted successfully |                                                                |
|     400       | Missing %prop% on request body  | Malformed Request, fix the issues and retry.                   |

#### `POST` Timestamp OUT

Creates a new **Exit** Timestamp.

```bash
{{URL}}/api/timestamp/in
```

##### Body

|  Field  |  Type  |                           Description                          |
| :-----: | :----: | :------------------------------------------------------------: |
| api_key | String | Your API Key.                                                  |
|   ts    | String | The timestamp in IFTTT "moment" format. Smart guess otherwise. |

##### Returns

|  Status Code  |            Message              |                           Reason                               |
| :-----------: | :-----------------------------: | :------------------------------------------------------------: |
|     200       | Timestamp inserted successfully |                                                                |
|     400       | Missing %prop% on request body  | Malformed Request, fix the issues and retry.                   |

#### `PUT` Timestamp EDIT

Edits an existing Timestamp.

```bash
{{URL}}/api/timestamp/id/{id}
```

##### Body

|  Field  |  Type  |                           Description                          |
| :-----: | :----: | :------------------------------------------------------------: |
| api_key | String | Your API Key.                                                  |
|   ts    | String | The new value of the timestamp being overriden.                |

##### Returns

|  Status Code  |            Message              |                           Reason                               |
| :-----------: | :-----------------------------: | :------------------------------------------------------------: |
|     200       | ok                              |                                                                |
|     400       | Missing %prop% on request body  | Malformed Request, fix the issues and retry.                   |

#### `DELETE` Timestamp DELETE

Edits an existing Timestamp.

```bash
{{URL}}/api/timestamp/id/{id}
```

##### Body

|  Field  |  Type  |                           Description                          |
| :-----: | :----: | :------------------------------------------------------------: |
| api_key | String | Your API Key.                                                  |

##### Returns

|  Status Code  |            Message              |                           Reason                               |
| :-----------: | :-----------------------------: | :------------------------------------------------------------: |
|     200       | ok                              |                                                                |
|     400       | Missing %prop% on request body  | Malformed Request, fix the issues and retry.                   |

## To Do

### Melhorias

- Adicionar observação diária

- Adicionar possibilidade de gerar nova API Key
