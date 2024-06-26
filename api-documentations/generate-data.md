# GenerateDataController API / GenerateDataCommand
<hr>

## Tartalomjegyzék
<!-- TOC -->
* [GenerateDataController API](#generatedatacontroller-api)
  * [Tartalomjegyzék](#tartalomjegyzék)
  * [Leírás](#leírás)
  * [URL](#url)
    * [GET](#get)
  * [Response](#response)
    * [GET](#get-1)
  * [Minta hívások](#minta-hívások)
    * [GET](#get-2)
      * [200 - OK](#200---ok)
      * [422 - Unprocessable Content](#422---unprocessable-content)
      * [422 - Unprocessable Content - email error](#422---unprocessable-content---email-error)
      * [400 - Bad Request](#400---bad-request)
<!-- TOC -->

## Leírás
Ez az API arra szolgál, hogy létrehozza az AWF Porsche projektjéhez szükséges adatbázis rekordokat a kapott CSV
fájlokból. Ezeket a CSV fájlokat a Porsche biztosítja az AWF számára, mi pedig tőlük kapjuk egy előre definiált
szerveren keresztül, előre definiált elérési úttal és meghatározott, állandó fájlnevekkel.<br />
Ezt a végpontot kézzel is meg lehet hívni a lent ismertetett módon, azonban a termelésben automatikusan hívódik minden
nap éjfélkor.

## URL
Itt kerülnek felsorolásra az API hívásához szükséges url-ek.

| Megnevezés                                          | Metódus | URL                                    |
|-----------------------------------------------------|---------|----------------------------------------|
| Adatok generálása és e-mailek küldése, ha szükséges | GET     | `/api/ext/awf-extension/generate-data` |

### GET
```
GET localhost:8000/api/ext/awf-extension/generate-data
Accept: application/json
Content-Type: application/json
```

## Response
Itt látható a GET metódus visszatérési struktúrája

### GET
```
{
   "success": bool,
   "data": array,
   "message": string
}
```

## Minta hívások
Néhány minta hívás és a hozzá tartozó válasz státusztól függően.
A minta hívások Windows rendszer alatt készültek, így curl helyett a gyári Invoke-WebRequest szolgáltatással készültek

### GET
`Invoke-WebRequest -Method GET -H @{"Content-Type"="application/json"} -Uri http://localhost:8000/api/ext/awf-extension/generate-data`

#### 200 - OK
```
{
    "success":true,
    "message":""
}
```

#### 422 - Unprocessable Content
```
{
    "success":false,
    "message":"Hiba a feldolgozás során!"
}
```

#### 422 - Unprocessable Content - email error
```
{
    "success":false,
    "message":"Hiba lépett fel az e-mail küldése során!"
}
```

#### 400 - Bad Request
```
{
    "success":false,
    "message":"Hibás kérés!"
}
```
