# CityPlay — Mode d'emploi du jeu

## Qu'est-ce que CityPlay ?

CityPlay est un jeu de découverte urbaine en plein air. Le joueur se déplace dans la ville (à pied, à vélo ou en voiture) pour trouver des lieux cachés et résoudre des énigmes.

---

## Règles du jeu

### 1. Créer une partie
- Choisis une ville (ex: Paris)
- Définis la durée (15 min à 5h)
- Choisis ton mode de locomotion (pied, vélo, voiture, moto)
- Choisis la difficulté (1 facile, 2 moyen, 3 difficile)
- Définis le nombre de joueurs (1 à 9)

### 2. Partager la partie
- Génère un code de liaison (ex: ABCD-1234)
- Partage le lien ou le code avec tes amis
- Les paramètres sont verrouillés après partage

### 3. Rejoindre une partie
- Saisis le code de 9 caractères (format XXXX-XXXX)
- Tu rejoins la partie en tant que membre
- Tu ne peux pas modifier les paramètres

### 4. Jouer
- Reçois une énigme et des coordonnées GPS
- Déplace-toi vers le lieu indiqué
- Valide ta position GPS quand tu es sur place
- Résous l'énigme ou demande un indice
- Passe à l'énigme suivante si bloqué

### 5. Fin de partie
- La partie se termine quand tous les lieux sont découverts
- Ou quand le temps est écoulé
- Ou si tu abandonnes

---

## Modes de jeu

| Mode | Description |
|------|-------------|
| Solo | 1 joueur, exploration libre |
| Team | 2 à 9 joueurs, même équipe |

---

## Difficulté des énigmes

| Niveau | Public | Exemple |
|--------|--------|---------|
| force1 | Facile | "Je suis le monument le plus célèbre de Paris" |
| force2 | Moyen | "Mon créateur s'appelle Gustave" |
| force3 | Difficile | "J'étais un palais royal avant 1793" |
| enfant | Enfant | "Je suis une grande tour en fer" |

---

## Actions disponibles

| Action | Endpoint API | Quand l'utiliser |
|--------|-----------|------------------|
| Démarrer | POST /api/parties/{id}/demarrer | Début de partie |
| Valider GPS | POST /api/parties/{id}/valider-gps | Sur le lieu |
| Passer énigme | POST /api/parties/{id}/passer-enigme | Bloqué |
| Indice | POST /api/parties/{id}/indice | Besoin d'aide |
| Solution | POST /api/parties/{id}/solution | Abandon énigme |
| Pause | POST /api/parties/{id}/pause | Pause toilettes |
| Reprendre | POST /api/parties/{id}/reprendre | Fin pause |
| Terminer | POST /api/parties/{id}/terminer | Abandon |

---

## Codes d'erreur

| Code | Signification | Solution |
|------|-------------|----------|
| 400 | Requête invalide | Vérifie les coordonnées GPS |
| 403 | Non autorisé | Tu n'es pas le créateur |
| 404 | Partie introuvable | Code invalide ou expiré |
| 422 | Validation échouée | Vérifie les champs envoyés |

---

## Dépendances entre devs

| Dev | Rôle | Ce qu'il te fournit | Ce que tu lui fournis |
|-----|------|---------------------|----------------------|
| Dev 1 (toi) | Backend API | — | Endpoints JSON stables |
| Dev 2 | Front Joueur | Écrans Vue.js | API pour énigme + feedback |
| Dev 3 | Admin | Paramètres TTL | Structure ProgressionPartie |

---

## Test rapide (curl)

```bash
# 1. Démarrer serveur
php artisan serve

# 2. Créer token
php artisan tinker --execute='$u=\App\Models\User::first();echo $u-&gt;createToken("demo")-&gt;plainTextToken;'

# 3. Démarrer partie
curl -X POST http://127.0.0.1:8000/api/parties/1/demarrer \
  -H "Authorization: Bearer TON_TOKEN"

# 4. Valider GPS Tour Eiffel
curl -X POST http://127.0.0.1:8000/api/parties/1/valider-gps \
  -H "Authorization: Bearer TON_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"lat":48.8584,"lng":2.2945,"precision":10}'









/////////informations de connexion //////////

  | Champ        | Valeur             |
| ------------ | ------------------ |
| Email        | <test@cityplay.fr> |
| Mot de passe | password           |
| Pseudo       | TestPlayer         |
