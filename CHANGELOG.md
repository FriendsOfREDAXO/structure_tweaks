# Changelog

## [2.0.0]

### Behoben

- **Metainfo im Tab:** Das Speichern der Artikel-Metadaten im eigenen Tab wurde an den stabilen Core-Workflow angepasst.
- **Deprecated-Meldung entfernt:** Die HTML-DOM-Verarbeitung in der Tab-Variante nutzt keine deprecated `mb_convert_encoding(..., 'HTML-ENTITIES', ...)`-Umwandlung mehr.
- **PHP-8.4-DOM-API:** DOM-Manipulationen nutzen jetzt `Dom\HTMLDocument` statt der alten `DOMDocument`-API.
- **Inline-JS bereinigt:** Die kleine Initialisierung auf der Kategorienseite läuft jetzt über das zentrale Asset statt über ein zusätzliches Inline-Script im Formular.
- **Bestehende Logikfehler korrigiert:** Doppelte Initialisierungen und unnötige `empty()`-Prüfungen wurden entfernt.

### Verbessert

- **Namespace-Migration:** Eigene Klassen liegen jetzt unter `FriendsOfREDAXO\StructureTweaks`.
- **REDAXO-Zielversion:** Das AddOn wird nur noch für REDAXO ab 5.19 gepflegt; alte Kompatibilitätszweige wurden zurückgebaut.
- **Script-Sicherheit:** Verbleibende Inline-Skripte im Backend tragen jetzt einen REDAXO-Nonce.
