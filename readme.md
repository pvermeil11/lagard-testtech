# tech test : REST API Symfony 4

## Install project

git clone this repository.
Move to lagardere-test and run :
```
composer install
```
Install and open postman, and then import the file named
```
lagardere-test.postman_collection.json
```
Also, import environment file :
```
lagardere-test.postman_environment.json
```

Before making your tests in the collection, run the built-in webserver by :
```
php bin/console server:start
```
And you can start reaching the API at
http://localhost:8000/your_endpoint

## JWT auth

**Please have a look in .env file for passphrase needed in your JWT. You'll have to generate SSH key with this passphrase like shown below :**

https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#generate-the-ssh-keys-

Lexik JWT is configured not to alter service URL. You can see its config in config/packages/security.yaml

See Lexik at :
https://github.com/lexik/LexikJWTAuthenticationBundle

## Cache management

Check in HTTP Headers in response that you have Cache-Control when calling GET endpoints, for example :
```
curl -X GET \
  http://127.0.0.1:8000/user/all \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NTU1MDg4MjcsImV4cCI6MTU1NTUxMjQyNywicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlciJ9.t9TAWkN-Ytw5xJwxOtFCX15DMG35W4FBA3VSQPvTG9AzeTAhIeaI_J7Uvhy_ybF8VpK6tCd9lmYXcrj9YSXyVIHdeavEKLFny828xyLLRvXJar0MJ9hQkK_hy51jmGVBtzU7YxFeOE5XJCb_oOkO9gXX_Y5oSOh02emaz2YhsR1LM_kv1W0NL9xtk7CGAAtLyP1io8MGUm1C6IeUFM6CPafK3Jsahnduc-_84edD0KNom4-AEfRco0dMaKM8zIGqWkp1dX6LR2wa3z13Vbe7z1oJcCW2zciVYnOdYP-_PFIWZXlv1ccy5d7LVySZdGHWYyq8Vn4Pomcpr716DjChbwHARiljYV-3BNSNsUMCjcQ-kO8oNtUItBp7tXaAWlQr-0ffBkWDAQJ6FzJEpA5HeXlyj7Hj2CI1oAj5Efd0zg4c7bpaciavv3fKDHZttYfKOOiQRBIlNl0iEb56OORryos1AuetZ3ALGKGEA5uufcZAI-iM6pB5-E3hGmUNTnO7-rN3MYQzU1IyFv_JQ8iTa2Bo7_hRcFRd4RStynkb1vzxzbQHjpQhXkbnD73WuFJCbMkT3ss8IEzGpqzGRQa_Eau5jxnklrS2wR3yTHYMerF-3Z4gJVujkyj5lICdDvPzQFqfSs5r4xGD-qvbXu7WY5eai0fdCfB3SItknJRpEmY' \
  -H 'Postman-Token: e14405db-643a-4fc2-8617-33e9d9258de5' \
  -H 'cache-control: no-cache'
```
See https://symfony.com/doc/current/http_cache.html#http-cache-validation-intro

## Used components

Symfony vardumper for easier debugging<br/>
Web-server-bundle for quick and easy web server without config<br/>
Symfony Validator<br/>
Symfony Stopwatch for execution time measuring
Lexik JWT library<br/>

Contact pvermeil@eleven-labs.com for any issue.