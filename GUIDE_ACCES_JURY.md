# Guide d'accès pour les membres du jury

## 🎯 ACCÈS DIRECT AU DASHBOARD JURY

### URL à utiliser :
```
http://votre-domaine/jury/dashboard
```

**OU** si vous êtes en local :
```
http://localhost/jury/dashboard
```

**OU** si vous utilisez Laravel Sail :
```
http://localhost:8000/jury/dashboard
```

---

## 📋 ÉTAPES POUR ACCÉDER

### 1. Connexion
- Allez sur la page de connexion : `/login`
- Connectez-vous avec votre compte **membre du jury**
- Vous serez automatiquement redirigé vers `/jury/dashboard`

### 2. Accès direct via URL
Si vous êtes déjà connecté, tapez directement dans votre navigateur :
```
/jury/dashboard
```

### 3. Vérification de votre rôle
Assurez-vous que votre compte utilisateur a bien le rôle **"jury"** :
- Vérifiez dans la base de données : table `role_user`
- Vérifiez que vous êtes membre d'un jury : table `jury_members`

---

## 🔍 CE QUE VOUS DEVRIEZ VOIR

Une fois sur `/jury/dashboard`, vous devriez voir :

### Section 1 : Candidatures en attente d'évaluation
- Liste des candidatures assignées aux jurys dont vous êtes membre
- Pour chaque candidature :
  - Nom du formateur
  - Nom du jury
  - Étape courante à évaluer
  - Bouton **"Noter cette étape"** (rouge) ou **"Voir/Modifier"** (gris si déjà noté)

### Section 2 : Candidatures prêtes pour validation président
- (Visible uniquement si vous êtes président du jury)
- Liste des candidatures dont toutes les étapes sont terminées
- Bouton **"Valider/Rejeter"** (jaune)

### Section 3 : Candidatures terminées
- Liste des candidatures validées ou rejetées
- Bouton **"Voir les évaluations"** (gris)

---

## ⚠️ SI VOUS NE VOYEZ RIEN

### Vérification 1 : Êtes-vous membre d'un jury ?
```sql
SELECT * FROM jury_members WHERE user_id = 'VOTRE_USER_ID';
```

### Vérification 2 : Y a-t-il des candidatures assignées à votre jury ?
```sql
SELECT c.* FROM candidatures c
INNER JOIN jury_candidature jc ON c.id = jc.candidature_id
INNER JOIN jury_members jm ON jc.jury_id = jm.jury_id
WHERE jm.user_id = 'VOTRE_USER_ID'
AND c.status = 'in_review';
```

### Vérification 3 : Le jury a-t-il une grille d'évaluation ?
```sql
SELECT * FROM juries WHERE id IN (
    SELECT jury_id FROM jury_members WHERE user_id = 'VOTRE_USER_ID'
);
```

---

## 🚀 ACCÈS RAPIDE POUR NOTER UNE ÉTAPE

Si vous connaissez l'ID de la candidature et de l'étape :
```
/jury/evaluate/{candidature_id}/{step_id}
```

Exemple :
```
/jury/evaluate/123e4567-e89b-12d3-a456-426614174000/123e4567-e89b-12d3-a456-426614174001
```

---

## 📞 EN CAS DE PROBLÈME

1. Vérifiez que vous êtes connecté
2. Vérifiez que votre compte a le rôle "jury"
3. Vérifiez que vous êtes membre d'un jury
4. Vérifiez qu'il existe des candidatures assignées à votre jury
5. Vérifiez les logs Laravel : `storage/logs/laravel.log`

---

## 🔗 LIENS UTILES

- Dashboard : `/jury/dashboard`
- Évaluer une étape : `/jury/evaluate/{candidature}/{step}`
- Validation président : `/jury/candidature/{candidature}/validate`
- Voir les évaluations : `/jury/candidature/{candidature}/view`







