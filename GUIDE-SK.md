# OrderDesk – vysvetlenie pre Dominika

## Čo to je

Jednoduchá evidencia objednávok. Zvolili sme ju preto, že vieš vysvetliť jej
praktický zmysel na základe skúseností s objednávkami. Nie je to starší školský
projekt: prvú verziu teraz vytvorila AI. Pred poslaním ju otestuj a pochop.

## Ako tečú údaje

1. Prehliadač otvorí `public/index.php` požiadavkou GET.
2. `database()` pripojí SQLite cez PDO a vytvorí tabuľku, ak ešte neexistuje.
3. PHP vyberie objednávky a vloží ich do HTML tabuľky.
4. Formulár pridania alebo zmeny stavu odošle POST.
5. Server skontroluje CSRF token a hodnoty; až potom spustí SQL.
6. Po úspechu presmeruje na GET (303), aby obnovenie stránky neposlalo formulár znovu.

## Čomu musíš rozumieť

- **PDO:** rozhranie PHP na prácu s databázou.
- **Prepared statement:** SQL má zástupné `?`; údaje sa posielajú oddelene cez
  `execute()`. Zadaný text sa tak nestane súčasťou SQL príkazu.
- **Escaping:** `e()` používa `htmlspecialchars`, aby sa text zákazníka nezmenil
  na HTML alebo spustiteľný skript. SQL ochrana a HTML ochrana riešia iné problémy.
- **CSRF:** náhodný token v session a formulári umožňuje serveru overiť formulár.
- **Validácia:** kontrola v HTML pomáha používateľovi, ale dá sa obísť.
  Rozhodujúca je kontrola na serveri.
- **Peniaze:** 19.99 EUR sa uloží ako 1999 centov. Dva kusy = 3998 centov.
- **Session:** uchováva token a jednorazovú správu o úspešnom uložžení.
- **SQLite:** databáza v lokálnom súbore; pre tento malý projekt netreba
  inštalovať samostatný databázový server. Nie je to MySQL.

## Spustenie

Potrebuješ PHP s PDO SQLite. Keď príkaz `php` v termináli nefunguje, PHP nie je
nainštalované alebo nie je v PATH. Po rozbalení otvor terminál v priečinku
`orderdesk` a spusti príkazy v README. Databáza sa vytvorí sama.
Na verejný server túto verziu nedávaj: nemá prihlasovanie.

## Prvé vlastné úpravy

1. Zmeň texty rozhrania na slovenčinu bez zmeny interných hodnôt stavov.
2. Pridaj do formulára a databázy nepovinnú poznámku s maximálnou dĺžkou.
   Pozor: CREATE TABLE IF NOT EXISTS nemení existujúcu tabuľku. Potrebuješ
   migráciu ALTER TABLE, prípadne na čistej demo databáze vytvorenie odznova.
3. Pridaj filter podľa minimálnej hodnoty objednávky cez pripravený SQL dotaz.
4. Vysvetli, prečo sa zrušená objednávka ráta do počtu, ale nie do hodnoty.

Vyber si jednu úpravu, urob ju a zapíš do README presne to, čo si skutočne
zmenil a otestoval. Nepridávaj vymyslenú históriu práce ani dátumy.

## GitHub odkaz do prihlášky

Vytvor verejný repozitár `orderdesk-php`, nahraj rozbalený obsah projektu
(nie ZIP ani lokálny databázový súbor) a ponechaj vysvetlenie AI asistencie.
Do prihlášky patrí URL repozitára. Na toto nemusí byť aplikácia online.
README nemá tvrdiť, že si niečo otestoval alebo napísal, kým to neurobíš.

Projekt ti sám osebe nepridá rok praxe. Ak máš menej ako rok reálnej práce
s PHP a formulár nemá príslušnú možnosť, treba to objasniť náborárovi.
