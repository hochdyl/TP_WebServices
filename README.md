
# TP_WebServices

Une API Symfony sur une collection de films.


## Run Locally

Clone the project

```bash
  git clone https://github.com/hochdyl/TP_WebServices.git
```

Go to the project directory

```bash
  cd TP_WebServices
```

Install dependencies with [Composer](https://getcomposer.org/)

```bash
  composer install
```

Change `.env` file with your database informations like

```bash
  DATABASE_URL="mysql://root:@127.0.0.1:3306/tp_webservices?serverVersion=5.7&charset=utf8mb4"
```

Install the project with the following lines

```bash
  php bin/console d:d:c

  php bin/console d:m:m

  php bin/console d:f:l
```

Run the server

```bash
  symfony serve
```
## Usage/Examples

Add `X-Output-Format` in request header to change output format

Supported formats :
- `json`
- `xml`