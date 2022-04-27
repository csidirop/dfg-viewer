# Kitodo.Presentation

## Installation mit DFG Viewer als Beispielanwendung

### Grundinstallation

Voraussetzung ist ein Server / PC mit **Linux**, **Apache 2.4**, **MariaDB** (_zu klären: funktioniert alternativ auch SQLite für Testinstallationen? Anscheinend nicht!_) und **PHP 7**, eine Konfiguration, die auch als [LAMP](https://de.wikipedia.org/wiki/LAMP_(Softwarepaket)) bezeichnet wird. Empfohlen werden aktuelle Linux-Distributionen von Debian, Ubuntu oder vergleichbare. [WSL](https://de.wikipedia.org/wiki/Windows-Subsystem_f%C3%BCr_Linux) unter Windows ist ebenfalls möglich.  

Superuser Rechte vorausgesetzt (`sudo` oder `sudo su`).  

#### Installation Apache 2.4  & MariaDB
    apt update  
    apt install apache2 mariadb-server   
Apache Service: `service apache2 status|start|stop|restart|reload`  
DB Service: `service mysql status|start|stop|restart`

#### Installation PHP
Zusätzlich sind die folgenden Pakete zu installieren:

    apt install libapache2-mod-php php-curl php-gd php-intl php-mysql php-xml php-zip


### Installation TYPO3

#### Datenbank und Datenbankbenutzer einrichten
Als nächstes wird die Datenbank erstellt und ein Nutzer für Typo3 angelegt:

    # Create new database "dfgviewer" with user "TYPO3":
    mysqladmin create dfgviewer
    mysql
    MariaDB [(none)]> GRANT ALL ON dfgviewer.* TO typo3@localhost IDENTIFIED BY 'password';
    MariaDB [(none)]> exit

#### Install TYPO3 release 9  
Nun wird TYPO3 Version 9.5 installiert. Die Installation muss (!) mit Composer erfolgen. Achtung! Neuere Versionen von TYPO3 werden momentan nicht unterstützt.

    # Install TYPO3 r9 in 'dfgviewer' folder:
    cd /var/www
    apt install composer
    composer create-project typo3/cms-base-distribution:^9 dfgviewer
    touch dfgviewer/public/FIRST_INSTALL
    chown -R www-data: dfgviewer
    cd /var/www/html
    ln -s ../dfgviewer/public/* .
    a2enmod php7.4

    # Add apache config:
    nano /etc/apache2/sites-available/dfgviewer.conf
    # write into the config file:
      <Directory /var/www/>
          AllowOverride All
      </Directory>

    # Enable config:
    a2ensite dfgviewer

    # restart apache2 service:
    service apache2 restart

#### Typo3 setup

Now connect to http://localhost/ (or http://localhost/typo3/install.php) with your webbrowser. You should see the [TYPO3 Install Tool](https://docs.typo3.org/m/typo3/guide-installation/9.5/en-us/QuickInstall/TheInstallTool/Index.html). Read and fix any problems shown in the environment overview.   
Eg. One of the problems (_max_execution_time_ _and max_input_vars_) is fixable this way:  
 
    #create new modification file: 
    nano /etc/php/7.4/mods-available/typo3.ini  

Adding: (or the resp. values the page shows [wiki](https://docs.typo3.org/m/typo3/tutorial-getting-started/main/en-us/SystemRequirements/Index.html))  

    ;Settings for Typo3:  
    max_execution_time=240  
    max_input_vars=1500  

Save the file (`Ctrl+S`), activate the mod and restart the server:

    phpenmod typo3 
    service apache2 restart 

Reload the page and follow the upcoming instructions. Btw: the DB User was set before to _typo3@localhost_ with PW _password_ !  
If you let the install tool create a page for testing purposes, you have to reset its page configuration:  
_Backend -> Site Management -> Sites -> [Created Site] -> Delete Site Configuration_  
Otherwise the site wont load correctly!

#### Typo3 image processing setup (_optional_)

    apt install ghostscript graphicsmagick graphicsmagick-imagemagick-compat  


### Installation DFG Viewer

Anschließend installiert man die TYPO3-Extension [DFG-Viewer](https://extensions.typo3.org/extension/dfgviewer/). Während der Installation werden drei Seiten erstellt: Die Stammseite, der Konfigurations-Ordner und die Hauptseite des Viewers.

    cd /var/www/dfgviewer
    sed -i s/7.2/7.4/ composer.json
    composer require slub/dfgviewer
    vendor/bin/typo3 extensionmanager:extension:install dlf
    vendor/bin/typo3 extensionmanager:extension:install dfgviewer

Nach einem reload sollte nun unter _Backend -> Web -> Page_ der Seitenbaum "_DFG Viewer_" mit der Seite "_Viewer_" vorhanden sein. 

#### Austausch mit ORC-On-Demand Testcode

    cd /var/www/dfgviewer/public/typo3conf/ext
	rm -R dfgviewer
	rm -R dlf
	git clone https://github.com/csidirop/kitodo-presentation
	git clone https://github.com/csidirop/dfg-viewer
	mv dfg-viewer/ dfgviewer
	mv kitodo-presentation/ dlf
	cd dfgviewer 
	git checkout ocr-test-04-26
	cd ../dlf
	git checkout ocr-test-3.3.x-04-26
	
#### DFG Viewer config
Es müssen zwei Typo3 Konfigurationseinstellungen gesetzt werden. Diese kann man entweder über das Backend machen oder direkt in typo3conf/LocalConfiguration.php:  

Weg 1: Backend  
Unter _Backend -> Admin Tools -> Settings -> Configure Installation-Wide Options_ werden alle Kofigurationen augelistet. Ganz oben lassen sich diese filtern.

1. _pageNotFoundOnCHashError_: Den Haken entfernen
2. _requireCacheHashPresenceParameters_: "tx_dlf[id], set[mets]" eintragen
3. Änderungen durch den Button "Write cofiguration" schreiben  

Weg 2: direkt
Im Installationsverzeichnis (zb. `/var/www/dfgviewer/public/typo3conf`) in die `LocalConfigurartion.php` folgende Werte in die Struktur einfügen:

    'FE' => [
      'cacheHash' => [
        'requireCacheHashPresenceParameters' => [
            'tx_dlf[id]',
            'set[mets]',
        ],
      ],
      'pageNotFoundOnCHashError' => false,
    ],

Wobei darauf geachtet werden muss, dass bereits vorhandene Schlüssel abgeändert werden müssen.

#### ID Fix
Als nächstes müssen die ID Konstanten des DFG-Viewers angepasst werden: 
1. Zunächst muss man die _Uid_ der Seite herausfinden. Diese findet man unter: _Backend_ -> _Web_ -> _Template_ -> _DFG Viewer_ -> Right-Click on _Viewer_ -> _Info_
2. Die _Uid_ muss man sich nun kopieren bzw. merken.
3. Nun muss man in die Einstellungen für den DFG-Viewer. Diese findet man unter: _DFG Viewer_ -> _Constant Editor_ (im Drop-Down-Menü (links/mittig) auswählbar) -> _Category: PLUGIN.TX_DFGVIEWER_
4. Unter der Einstellung _config.kitodoPageView_ muss nun in dem vorgesehenen Feld die _Uid_ eingetragen werden.

#### Eingabefeld hinzufügen
Im Backend _Web -> Page -> DFG-Viewer_ den Button "+ Content" klicken und ein HTML Objekt hinzufügen. Anschließend folgenden Code eintragen:

    <div class="abstract">
	<form method="get" action="index.php">
	  <div>
		<label for="mets">Fügen Sie hier den Link zu Ihrer <acronym title="(engl.) metadata encoding and transmission standard; (dt.) Metadatenkodierungs- und -übertragungsstandard">METS</acronym>-Datei bzw. <acronym title="(engl.) open archives initiative; (dt.) Initiative für freien Datenaustausch">OAI</acronym>-Schnittstelle ein:</label><br />
		<input type="hidden" name = "id" value = "2">
		<input type="text" class="url" name="tx_dlf[id]" value="" /><br />
		<input type="hidden" name="no_cache" value="1" />
		<input type="submit" class="submit" value="Demonstrator aufrufen!" />
	  </div>
	</form>
	</div>

Wobei ggfs. die id anhand der UID angepasst werden muss.
	
#### TSConfig anpassen
Im Backend _Web -> Page -> DFG-Viewer -> Viewer -> Eigenschaften bearbeiten -> Resources_ 
1. Die TypoScript Configuration um die Extension dfgviewer aus dem rechten Feld ergänzen
2. Die Page TSConfig um die Zeile `TCEMAIN.permissions.groupid = UID` wobei UID die Id aus dem vorherigen Schritt ist.

#### Extension settings anpassen
Im Backend _Admin Tools -> Settings -> Extension Configuration -> dlf_ lassen sich Einstellungen zu Kitodo.Presentation eisntellen. Unter dem Tab _Fulltextocr_ müssen nun alle Einstellungen überprüft und ggfs. angepasst werden. 

Beispielsweiße:
  - fulltextocr.fulltextFolder = fulltextFolder
  - fulltextocr.fulltextTempFolder = _temp_/fulltextTempFolder
  - fulltextocr.fulltextImagesFolder = _temp_/imagesTempFolder
  - fulltextocr.ocrDummy = true / Haken setzen
  - fulltextocr.ocrEngine = tesseract
  - fulltextocr.ocrLanguages = frak2021_1.069
  - fulltextocr.ocrOptions = alto
  - fulltextocr.ocrDelay = 10
  - fulltextocr.ocrLock = true / Haken setzen

#### Kurzinstallation Tesseract v5
    sudo apt install tesseract
Unter Ubunutu 20.04 wird aktuell noch die veraltete Version 4 gelistet. Um die neuste Version zu installieren muss folgendes [Repo](https://ubuntuhandbook.org/index.php/2021/12/install-tesseract-ocr-5-ubuntu/) hinzugefügt werden: `sudo add-apt-repository ppa:alex-p/tesseract-ocr5` .
Um gute OCR Ergebnisse mit historischen Drucken zu erreichen empfiehlt es sich eine dafür spezialisiertes Model zu installieren. Aktuelle Modelle kann man bei der [Uni Mannheim](https://ub-backup.bib.uni-mannheim.de/~stweil/tesstrain/frak2021/tessdata_fast/) bekommen. Diese legt man untert `/usr/share/tesseract-ocr/5/tessdata/` ab.

    cd /usr/share/tesseract-ocr/5/tessdata/
    wget https://ub-backup.bib.uni-mannheim.de/~stweil/tesstrain/frak2021/tessdata_fast/frak2021_1.069.traineddata
     
Mit `tesseract --list-langs` kann man überprüfen welche Modelle vorhanden sind.

### Test 
#### Allgemein
Der Aufruf folgender Seite 
http://localhost/index.php?id=2&tx_dlf%5Bpage%5D=1&tx_dlf%5Bdouble%5D=0&tx_dlf%5Bid%5D=https%3A%2F%2Fdigital.slub-dresden.de%2Foai%2F%3Fverb%3DGetRecord%26metadataPrefix%3Dmets%26identifier%3Doai%3Ade%3Aslub-dresden%3Adb%3Aid-263566811&tx_dlf%5Bpagegrid%5D=1&cHash=3deb716062d5ea61c9640e5c5c5711dd 
sollte die Übersicht eines Digitalisates der SLUB Dresden öffnen. (Gegebenenfalls muss die id im Link angepasst werden und auf die Uid des Viewers gesetzt werden.)

Weitere Information und Beispiele kann man [hier](https://extensions.typo3.org/extension/dfgviewer/) anschauen.

#### Eingabefeld
Die Stammseite http://localhost/index.php?id=1 aufrufen und im Eingabefeld den Link zu einer Mets Datei eingeben. 
  - Volltext vorhanden: https://digi.bib.uni-mannheim.de/fileadmin/digi/1652998276/1652998276.xml
  - Volltext nicht vorhanden*: https://digi.bib.uni-mannheim.de/fileadmin/vl/ubmaosi/59087/59087.xml

(* Volltext ist im falschen Format und deshalb nicht erkannt)

## Links

* https://github.com/slub/dfg-viewer/
* https://github.com/kitodo/kitodo-presentation
* https://wiki.typo3.org/MySQL_configuration
* https://get.typo3.org/version/9.5.20
* https://docs.typo3.org/m/typo3/guide-installation/master/en-us/QuickInstall/Composer/Index.html
* https://extensions.typo3.org/extension/dfgviewer/
* https://docs.typo3.org/p/slub/dfgviewer/5.1/en-us/
