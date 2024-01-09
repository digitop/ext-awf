# SequenceController API
<hr>

## Tartalomjegyzék
<!-- TOC -->
* [SequenceController API](#sequencecontroller-api)
  * [Tartalomjegyzék](#tartalomjegyzék)
  * [Leírás](#leírás)
  * [URL](#url)
    * [Összes adat lekérése](#összes-adat-lekérése)
      * [GET](#get)
    * [Munkaállomás szerinti lekérés](#munkaállomás-szerinti-lekérés)
      * [GET](#get-1)
  * [Response - Összes adat lekérése](#response---összes-adat-lekérése)
    * [GET](#get-2)
  * [Minta hívások - Összes adat lekérése](#minta-hívások---összes-adat-lekérése)
    * [GET](#get-3)
      * [200 - OK](#200---ok)
      * [422 - Unprocessable Content](#422---unprocessable-content)
  * [Minta hívások - Munkaállomás szerinti lekérés](#minta-hívások---munkaállomás-szerinti-lekérés)
    * [GET](#get-4)
      * [200 - OK](#200---ok-1)
      * [422 - Unprocessable Content](#422---unprocessable-content-1)
<!-- TOC -->

## Leírás
Ez az API arra szolgál, hogy létrehozza az AWF Porsche projektjéhez szükséges adatstuktúrát. Ezt az adatstruktúrát a
Porsche biztosítja az AWF számára, mi pedig tőlük kapjuk. Ezen a végponton ezen adatok Node-RED számára történő
eljuttatása történik egy előre definiált JSON objektumban. 

## URL
Itt kerülnek felsorolásra az API hívásához szükséges url-ek.

| Megnevezés                              | Metódus | URL                                             |
|-----------------------------------------|---------|-------------------------------------------------|
| Összes adat lekérése                    | GET     | `/api/ext/awf-extension/get-default-sequence`   |
| Munkaállomás specifikus adatok lekérése | GET     | `/api/ext/awf-extension/get-sequence/{WCSHNA}`  |

### Összes adat lekérése
#### GET
```
GET localhost:8000/api/ext/awf-extension/get-default-sequence
Accept: application/json
Content-Type: application/json
```
### Munkaállomás szerinti lekérés
#### GET
```
GET localhost:8000/api/ext/awf-extension/get-sequence/{WCSHNA}
Accept: application/json
Content-Type: application/json
```

## Response - Összes adat lekérése
Itt látható az Összes adat lekéréséhez szükséges GET metódus visszatérési struktúrája

### GET
```
{
   "success": bool,
   "data": array,
   "message": string
}
```

## Minta hívások - Összes adat lekérése
Néhány minta hívás és a hozzá tartozó válasz státusztól függően.
A minta hívások Windows rendszer alatt készültek, így curl helyett a gyári Invoke-WebRequest szolgáltatással készültek

### GET
`Invoke-WebRequest -Method GET -H @{"Content-Type"="application/json"} -Uri http://localhost:8000/api/ext/awf-extension/get-default-sequence`

#### 200 - OK
```
{
  "success": true,
  "data": {
    "C": [
      {
        "SEPONR": "2042428",
        "SEPSEQ": "452089",
        "SEARNU": "F46B28101",
        "SESIDE": "L",
        "EL_image": null,
        "HE_image": "localhost:8000\/storage\/product\/F46B28101\/features\/images\/TEKEHE_9b0b27e9-ac09-4f57-9e0a-fc63fb7c2a23.jpg",
        "SZASZ": "000000",
        "SZAA": "dinamica"
      },
      {
        "SEPONR": "2042428",
        "SEPSEQ": "452089",
        "SEARNU": "F46B29101",
        "SESIDE": "R",
        "EL_image": null,
        "HE_image": "localhost:8000\/storage\/product\/F46B29101\/features\/images\/TEKEHE_9b0b27e9-ac09-4f57-9e0a-fc63fb7c2a23.jpg",
        "SZASZ": "000000",
        "SZAA": "dinamica"
      },
      {
      .
      .
      .
      }
    ]
  },
  "message": ""
}
```

#### 422 - Unprocessable Content
```
{
  "success": false,
  "data": [],
  "message": "responses.no_new_data_available"
}
```

## Minta hívások - Munkaállomás szerinti lekérés
Néhány minta hívás és a hozzá tartozó válasz státusztól függően.
A minta hívások Windows rendszer alatt készültek, így curl helyett a gyári Invoke-WebRequest szolgáltatással készültek

### GET
`Invoke-WebRequest -Method GET -H @{"Content-Type"="application/json"} -Uri http://localhost:8000/api/ext/awf-extension/get-sequence/EL01`

#### 200 - OK
```
{
  "success": true,
  "data": {
    "C": [
      {
        "SEPONR": "2742658",
        "SEPSEQ": "452110",
        "SEARNU": "F46B04304",
        "SESIDE": "L",
        "EL_image": null,
        "HE_image": "localhost:8000\/storage\/product\/F46B04304\/features\/images\/TEKEHE_9b0b27e9-ac09-4f57-9e0a-fc63fb7c2a23.jpg",
        "SZASZ": "323137",
        "SZAA": "stoff"
      },
      {
        "SEPONR": "2742658",
        "SEPSEQ": "452110",
        "SEARNU": "F46B05304",
        "SESIDE": "R",
        "EL_image": null,
        "HE_image": "localhost:8000\/storage\/product\/F46B05304\/features\/images\/TEKEHE_9b0b27e9-ac09-4f57-9e0a-fc63fb7c2a23.jpg",
        "SZASZ": "323137",
        "SZAA": "stoff"
      }
    ]
  },
  "message": ""
}
```

#### 422 - Unprocessable Content
```
{
  "success": false,
  "data": [],
  "message": "responses.no_new_data_available"
}
```
