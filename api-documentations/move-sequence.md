# MoveSequenceController API
<hr>

## Tartalomjegyzék
<!-- TOC -->
* [MoveSequenceController API](#movesequencecontroller-api)
  * [Tartalomjegyzék](#tartalomjegyzék)
  * [Leírás](#leírás)
  * [URL](#url)
    * [POST](#post)
  * [Request](#request)
    * [POST](#post-1)
  * [Response](#response)
    * [POST](#post-2)
  * [Minta hívások](#minta-hívások)
    * [POST](#post-3)
      * [200 - OK](#200---ok)
      * [422 - Unprocessable Content](#422---unprocessable-content)
<!-- TOC -->

## Leírás
Ez az API arra szolgál, hogy léptesse az AWF Porsche projektjéhez szükséges adatstuktúrát. Ezt az adatstruktúrát a
Porsche biztosítja az AWF számára, mi pedig tőlük kapjuk. Ezen a végponton ezen adatok Node-RED oldaláról kapott adatok 
alapján lépteti a szekvenciát. <br />
Letárolja a CUSTOM adatbázisba a gép változtatást, átrakja a következő gép queue-jába és visszaadja a következő 
szekvenciát az adott géphez.  

## URL
Itt kerülnek felsorolásra az API hívásához szükséges url-ek.

| Megnevezés | Metódus | URL                                    |
|------------|---------|----------------------------------------|
| Léptetés   | POST    | `/api/ext/awf-extension/move-sequence` |


### POST
```
POST localhost:8000/api/ext/awf-extension/move-sequence
Accept: application/json
Content-Type: application/json
```

## Request
Itt látható a POST metódus hívásakor kötelezően átadandó adatok struktúrája

### POST
```
{
  "WCSHNA": "EL01",
  "SEQUID": 48909
}
```

## Response
Itt látható a POST metódus visszatérési struktúrája

### POST
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

### POST
`Invoke-WebRequest -Method GET -H @{"Content-Type"="application/json"} -Uri http://localhost:8000/api/ext/awf-extension/move-sequence`

#### 200 - OK
```
{
  "success": true,
  "data": {
    "B": [
      {
        "SEPONR": "2741957",
        "SEPSEQ": "450194",
        "SEARNU": "F46B02103",
        "SESIDE": "L",
        "EL_image": null,
        "HE_image": "localhost:8000\/storage\/product\/F46B02103\/features\/images\/TEKEHE_9b0b27e9-ac09-4f57-9e0a-fc63fb7c2a23.jpg",
        "color": "000000",
        "material": "stoff"
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
