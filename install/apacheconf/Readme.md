Installation de surveyOctopus
-----------------------------

#Configuration d'apache

SurveyOctopus utilise le module de réécriture d'url d'apache2. Vous devez vous
assurer que ce module est activé.
Vous pouvez l'activer avec la commande suivante:
```shell
sudo a2enmode rewrite
```

N'oubliez pas de recharger apache2:
```shell
sudo service reload
```
SurveyOctopus utilise les fichiers .htaccess.

#Hôte vituel

Deux exemples sont à votre disposition dans ce répertoire.
Pour utiliser une de ces configurations, vous devrez copier l'une des deux dans
le répertoire
```shell /etc/apache2/sites-availables```

**Important**: 
Le site doit être installé dans ```shell /var/www/surveyOctopus``` pour que ces 
configurations, telles qu'elles sont, puissent fonctionner.

**default** contient une configuration pour un usage sur un serveur dans le but de
remplacer la configuration par défaut.	Copiez le fichier de la configuration:

```shell
sudo cp default/surveyOctopus.conf
```

Une fois la copie de la configuration effectuée, il vous suffira simplement
d'activer le site puis de recharger les configurations d'apache. Comme cette
configuration remplace celle par défaut celle-ci sera désactiée.

```shell
sudo a2ensite surveyOctopus
sudo a2dissite 000-default
sudo service apache2 reload
```

Vous pourrez accéder au site depuis l'adresse de votre serveur:
http://<adresse de votre serveur>/surveyOctopus

   **surveyoctop.us** contient une configuration pour un usage sur un ordinateur
   de bureau ou portable. Notez toutefois que la configuration précédente
   pourrait également fonctionner.

L'installation est similaire à celle ci-dessus mais inutile de désactiver la
configuration par défaut.

```shell
sudo a2ensite surveyOctopus
sudo service apache2 reload
```

Vous pourrez accéder au site depuis l'adresse: http://surveyoctop.us

