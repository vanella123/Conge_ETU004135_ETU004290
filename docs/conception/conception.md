# régime alimentaire adapté selon ses objectifs


## Fonctionnalite:
### Frontoffice

- inscription/connexion: en 2 pages differentes

- info regime: objectif (firy kg) + duree
- Info santé : taille et poids et IMC
- suggestion regime = sakafo + activite sportives selon objectif et duree du regime --> export PDF
- On peut rajouter de l’argent dans son porte monnaie en rentrant un code generique
- option Gold --> reduction prix regime

- graphe poids

## Backoffice
- authentification
- Tableau de bord, statistiques (graphe et tableau croise):
    - nb utilisatuer
    - nb gold
    - nb augemter/reduire/IMC 

- Pages CRUD:
    - CRUD sakafo
    - CRUD activite sportives
    - CRUD regimes: 
        - filtre duree / prix
        - filtre augmentation / reduction poids
        - Ajouter regime: % de viande, % de poisson, % de volaille
    - CRUD parametres necessaires:
        - codes
        - constitution d'un regime (ajouter legume par exemple)
    -  CRUD Option:
       -  GOLD: taux, type (permanent), prix
       -  ajouter option
       -  ...

- validation code

# DB

Utilisateur:
- id
- nom,
- email,
- mot de passe (haché), 
- genre, 
- taille, 
- poids, 
- date_naissance, 
- id objectif
- is_gold
- argent
- role (admin, client)

Objectif:
- id
- label

Regime:
- id
- nom
- variation poids (-/+)
- % viande
- % poisson
- % volaille

Table Commande;
id_user, id_regime, id duree, date_achat, montant_paye

Duree_Regime:
- id regime
- nb de jours
- prix

Duree_Regime_Prix (denormalisation):
- prix
- date

Codes:
- id
- montant
- code
- statut (deja_utilise)
- id utilisateur utilise par.. (NULL)

Regime_Activite:
- id 
- id activite sportives
- id regime 

Activite sportives:
- id
- label
- nb/semaines

Option:
- Gold:
    - nb de regime a achete
    - 20 000 Ar prix en une seule fois a vie.
    - 15 % de reduction sur le prix du regime
  
Option_Historique:
- id (PK)
- id_option (FK vers Option_Gold)
- prix
- reduction_pourcent
- nb de regime a achete
- date_debut (DATE)

# Amelioration:
## frontoffice
- tableau de bord de progression: enregistrer du poids par jours (automatique)

## Backoffice
- firy pourcent nahavita hatram farany ny objectif
