# AWF egyedit fejlesztésű modulja

Ez egy kiegészítő | plugin | extension modul a fő OEEM Laravel-es rendszerbe épül és új funkcionalitással egészíti ki
a fő rendszert; amely az AWF számára lett létrehozva.

## Tartalomjegyzék
- [Telepítés](#telepítés)
    - [Symbolic link létrehozása](#symbolic-link-létrehozása)
  - [ServiceProvider](#serviceprovider-felvétele-az-configappphp-fájl-providers-tömbjébe)
  - [Autoload újragenerálása](#autoload-fájl-újragenerálása)
  - [Cache ürítés](#cache-ürítése)
  - [Migrációk futtatása](#migrációk-futtatása-és-javascript-fájlok-publikálása)
- [Használat](#használat)
- [Fejlesztés](#fejlesztés)
    - [Git clone](#git-repo-lehúzása)
    - [Autoload újragenerálása](#autoload-fájl-újragenerálása-1)
- [Fontos tudnivalók](#fontos-tudnivalók)

## Telepítés

### Symbolic link létrehozása
Szükség van a háttérképek megjelenítéséhez egy symbolic linkre a storage public mappájáról a public könyvtárba.
Amennyiben még nincs ilyen létrehozva, úgy egyszerűen a `php artisan storage:link` paranccsal létrehozhatjuk

### ServiceProvider felvétele az `config/app.php` fájl `providers` tömbjébe:

```
'providers' => [
  AWF\Extension\Providers\ServiceProvider::class,
]
```

`composer.json` fájl `autoload` kiegészítése:

```
"autoload": {
  "psr-4": {
    "AWF\\Extension\\": "packages/awf/extension/src"
  }
}
```

### Autoload fájl újragenerálása

```composer dump-autoload```

### Cache ürítése

```php artisan cache:clear```

### Migrációk futtatása és javascript fájlok publikálása

```php artisan oeem:install-extension awf-extension```

## Használat

## Fejlesztés

### Git repo lehúzása
Az oeem projekt gyökér mappájában lévő `packages/awf` mappába. Tehát a mappaszerkezet így néz ki ezek után:

```
oeem/
    ...,
    packages/
        awf/
            extension
```

`composer.json` fájl `autoload` kiegészítése/módosítása:

```
"autoload": {
  "psr-4": {
    "AWF\\Extension\\": "packages/awf/extension/src"
  }
}
```

### Autoload fájl újragenerálása

```composer dump-autoload```

**Fejlesztés** majd változtatások után commit és feltöltés a remote repoba.

