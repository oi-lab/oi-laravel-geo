# Guide de migration - Support JSON pour MySQL/SQLite

## Changements importants

### Stockage des géométries

Le package a été mis à jour pour mieux supporter MySQL et SQLite en utilisant des colonnes JSON au lieu des types géométriques natifs qui n'existent pas sur ces bases de données.

#### Avant (v1.x)

Les migrations généraient toujours des colonnes `POINT` et `POLYGON` :

```php
$table->point('location')->nullable();
$table->polygon('boundary')->nullable();
```

**Problème** : Ces types n'existent pas en SQLite et ont un support limité en MySQL.

#### Maintenant (v2.x)

Les migrations s'adaptent au type de base de données :

**PostgreSQL** (avec PostGIS) :
```php
$table->point('location')->nullable();    // Type natif
$table->polygon('boundary')->nullable();  // Type natif
```

**MySQL / SQLite** :
```php
$table->json('location')->nullable();     // Format JSON
$table->json('boundary')->nullable();     // Format JSON
```

### Casts automatiques

Les traits `HasPoint` et `HasPolygon` ajoutent maintenant automatiquement des casts personnalisés :

- **PostgreSQL** : Utilise les fonctions spatiales natives (ST_GeomFromText, etc.)
- **MySQL/SQLite** : Stocke et récupère les données en JSON

### Format des données

#### POINT

**PostgreSQL (WKT)** :
```
POINT(2.3522 48.8566)
```

**MySQL/SQLite (JSON)** :
```json
{
  "longitude": 2.3522,
  "latitude": 48.8566
}
```

**Utilisation dans le code** (identique pour toutes les DB) :
```php
$city->location = [2.3522, 48.8566];
// ou
$city->location = ['longitude' => 2.3522, 'latitude' => 48.8566];

// Récupération
$latitude = $city->latitude;   // 48.8566
$longitude = $city->longitude; // 2.3522
```

#### POLYGON

**PostgreSQL (WKT)** :
```
POLYGON((2.3 48.8, 2.4 48.9, 2.5 48.8, 2.3 48.8))
```

**MySQL/SQLite (JSON)** :
```json
[
  [2.3, 48.8],
  [2.4, 48.9],
  [2.5, 48.8]
]
```

**Utilisation dans le code** (identique pour toutes les DB) :
```php
$department->boundary = [
    [2.3, 48.8],
    [2.4, 48.9],
    [2.5, 48.8],
];

// Récupération
$coordinates = $department->boundary_coordinates;
```

## Migration depuis v1.x

### 1. Réinstaller le package

Si vous avez déjà installé le package avec d'anciennes migrations :

```bash
# Supprimer les anciennes migrations
rm database/migrations/*_create_countries_table.php
rm database/migrations/*_create_regions_table.php
rm database/migrations/*_create_departments_table.php
rm database/migrations/*_create_cities_table.php
rm database/migrations/*_create_boroughs_table.php
rm database/migrations/*_create_addresses_table.php

# Réinstaller avec la nouvelle commande interactive
php artisan geo:install
```

### 2. Sélectionner votre type de base de données

Lors de l'installation, vous serez invité à sélectionner votre type de base de données :

- **PostgreSQL with PostGIS** : Utilise les types géométriques natifs
- **MySQL 5.7+** : Utilise des colonnes JSON
- **SQLite** : Utilise des colonnes JSON

### 3. Migrer

```bash
php artisan migrate
```

## Limitations

### MySQL/SQLite

Avec le stockage JSON, certaines opérations géométriques avancées sont simplifiées :

- **Points** : Les requêtes `nearby()`, `withinBounds()` fonctionnent avec la formule Haversine
- **Polygons** : Les requêtes `containsPoint()`, `intersects()` sont simplifiées (retournent juste les polygones existants)

Pour des opérations géométriques avancées et précises, **il est fortement recommandé d'utiliser PostgreSQL avec PostGIS**.

### PostgreSQL

Avec PostGIS, toutes les fonctionnalités géométriques sont disponibles :

- Calcul précis des distances
- Test d'inclusion dans les polygones
- Intersections de polygones
- Calcul de surface
- etc.

## Nouveaux casts

Deux nouveaux casts ont été ajoutés :

- `OiLab\OiLaravelGeo\Casts\PointCast` : Pour les colonnes de type POINT/JSON
- `OiLab\OiLaravelGeo\Casts\PolygonCast` : Pour les colonnes de type POLYGON/JSON

Ces casts sont automatiquement appliqués lorsque vous utilisez les traits `HasPoint` et `HasPolygon`.

## Support

Si vous rencontrez des problèmes avec la migration, n'hésitez pas à ouvrir une issue sur GitHub.
