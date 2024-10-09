# Fejlesztői próbafeladat

- A próbafeladat két részből áll:
    - Egy egyszerű PHP alkalmazás megvalósításából
    - És egy SQL lekérdezés írásából
- A megoldásra összesen legfeljebb 3 órát szánj!
- A megoldást egy git repo formájában küldd el nekünk (GitHub, GitLab stb.) 5 napon belül.

# Első rész: PHP

A feladatot PHP nyelven oldd meg. A megvalósítás során tetszőleges keretrendszer vagy egyéb függőség használható.

A megvalósítandó program termékeket tart nyilván, amelyek tetszőleges számú (0..n) termékkategóriához tartozhatnak. A programnak két elvárt funkciója van:

- Termékek és termékkategóriák importálása CSV fájlból egy általad kialakított adatbázisba
- XML termék feed generálása az adatbázisból

## 1. CSV importálása

A csatolt `termekek.csv` fájl az alábbi adatokat tartalmazza:

- Termék megnevezése
- Bruttó ár (pozitív egész szám)
- Kategória 1 (opcionális)
- Kategória 2 (opcionális)
- Kategória 3 (opcionális)

A program a CSV beolvasásával szinkronizálja a termékek adatbázisát:

- Amennyiben egy termék vagy termékkategória nem létezik, kerüljön létrehozásra
- Amennyiben egy termék létezik, frissüljön az ára és a termékkategória-hozzárendelései
- A termékeket és a kategóriákat is megnevezés alapján azonosítjuk

## 2. Termékfeed (XML) generálása

A program az adatbázisban található összes termékből generáljon egy termék feedet XML formátumban. Az elvárt formátum a következő:

```xml
 <?xml version="1.0" encoding="UTF-8"?>
 <products>
	 <product>
		 <title>Termék megnevezése</title>
		 <price>1990</price>
		 <categories>
			 <category>Téli ruha</category>
			 <category>Őszi ruha</category>
		 </categories>
	 </product>
		 <!-- ... további <product> bejegyzések -->
 </products>
```

# Második rész: MySQL / MariaDB

A feladat megoldásához MySQL 8+, vagy MariaDB 10.10+ adatbázis használandó.

- A `products` táblában termékek szerepelnek
- A termékek ára minden árváltozáskor a `price_history` táblába kerül elmentésre az aktuális időbélyeggel együtt
- A `product_packages` termékcsomagokat határoz meg. Egy termékcsomag több különböző termékből áll
    - A termékcsomagok tartalmát a `product_package_contents` tábla tárolja
    - Egy termékcsomag egy termékből több darabot is tartalmazhat. A mennyiséget a `quantity` oszlop adja meg
    - A termékcsomagok ára mindig a termékcsomagban foglalt termékek árainak az összege
- A teszt adatbázisban 3 hónapnyi ártörténet szerepel (2024-01-01 - 2024-03-31)
- A feladat megoldása során feltételezhető, hogy minden termékhez tartozik legalább egy ár
    - A teszt adatbázisban 2024-01-07-től kezdve minden termékhez tartozik legalább egy bejegyzés

## Feladat: 
Írj egy SQL lekérdezést, amely egy termékcsoport-azonosító és egy időpont alapján visszaadja a termékcsoport adott időpontban érvényes árát.
