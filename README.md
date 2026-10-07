# -prj_web_2425_t2

# ReadyPC

## Description

Projet réalisé en BAC1 durant l'année scolaire 2024 - 2025 dans le cadre de nos études.

ReadyPC est un site de vente de PC pré-montés et prêts à l'emploi.  
Il propose des configurations adaptées à tous les budgets et à tous les profils d'utilisateurs.

## Membres du projet

- **Yassin Harkat** - [yassin.harkat@student.helb-prigogine.be](mailto:yassin.harkat@student.helb-prigogine.be)  
- **Akim Belkouaci** - [akim.belkouaci@student.helb-prigogine.be](mailto:akim.belkouaci@student.helb-prigogine.be)  
- **Alexandre Caira** - [alexandre.caira@student.helb-prigogine.be](mailto:alexandre.caira@student.helb-prigogine.be)  
- **Mohamed Debboun** - [mohamed.debboun@student.helb-prigogine.be](mailto:mohamed.debboun@student.helb-prigogine.be)  

---

## Fonctionnalités du projet

1. Système de connexion  
2. Système d'inscription  
3. Déconnexion  
4. Suppression du compte utilisateur  
5. Barre de recherche (système de recherche)  
6. Système de filtres (PC gaming / PC de bureau / Tranche de prix)  
7. **Gestion des PC (compte admin requis)**  
   - Ajout de PC dans la base de données (nom, carte graphique, prix, etc.)  
   - Modification des PC existants  
   - Suppression des PC  
8. CSS des pages de connexion / inscription / déconnexion / gestion de PC  
9. Page de détails pour chaque PC  
10. Système de panier (achat de produits avec message de confirmation et bouton de retour)  
11. Création d'une page Profil(pouvoir changer son nom d'utilisateur,mot de passe et d'avoir la posibilité de mettre une photo de profil) 
12. Version mobile du site (Bootstrap)  
13. Section commentaires et forum (police et taille adaptées)  
14. Affichage du prix de l'installation 
15. Système de comparaison entre les pc
16. Formulaire de paiement après l'achat d'un produit

---

## Déploiement

1. Start Apache and MySQL in XAMPP
2. Import `database/ready_pc.sql` using phpMyAdmin. The `ready_pc` database will be created automatically.
3. Copy `config.example.php` to `config.php`
4. Enter your MySQL credentials in `config.php`
5. Put the project in `C:\xampp\htdocs\`
6. Open http://localhost/ReadyPC/

