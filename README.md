# APA-IT VideoService - Wordpress Plugin.

Dieses Plugin stellt die Grundfunktionalitaet zur Verfuegung um Videos aus dem APA IT VideoService darzustellen.

## Installtion:

Kopieren Sie die Files in das .../wp-content/plugins Verzeichnis Ihrer Wordpress Installation.

z.B. 
git clone https://github.com/apa-it/wordpress-videoservice.git

Danach sollte in den installierten Plugins APA-IT VideoService erscheinen. Sie muessen es nur noch aktivieren, und danach sollte alles Einsatzbereit sein.

## Verwendung:

In einem Textblock koennen Sie das Plugin in einem

[uvp...] Block verwenden.

### Parameter:
>   guid: die eindeutige ID des Videos oder Livestreams. Zu finden entweder per Notification oder Portal.
>   secure (optional, default: false): Wenn dieser Parameter auf true gesetzt wird, wird ein Parameter mit einem Secure Token
>                                      dem Aufruf hinzugefuegt. Dazu werden die IP Adresse des Aufrufers, und die Guid genommen,
>                                      und mit dem Ablaufdatum (aktuelle Zeit plus ein Tag) und eine eindeutige ID genommen, und
>                                      mit dem per Setting eingetragenen Secure Token als JWT erstellt.
>   width: Die Breite die das Video auf der Oberflaeche haben soll, default ist 640
>   height: Die Hoehe die das Video auf der Oberflaeche haben soll, default ist 380


## Beispiel:

   [uvp guid='{VideoServiceGUID}' secure=true]



