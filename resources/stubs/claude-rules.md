# OI Laravel Geo

Use the `oi-laravel-geo` package for hierarchical geographic data (Country →
Region → Department → City → Borough), Address management, GeoJSON import and
spatial queries in this Laravel application. Models are configurable and
resolved through the `OiLaravelGeo` facade; every model exposes a typed
`toData()` DTO.

- IMPORTANT: Activate `oilab-laravel-geo` when working with countries, regions,
  departments, cities, boroughs, addresses, GeoJSON seeding (`geo:seed`), or
  Point/Polygon spatial queries in this project. Resolve model classes via
  `OiLaravelGeo::get*Model()`, never hardcode them. Addresses can optionally be
  attached to any model (`address_morphable` + the `HasAddresses` trait), keyed
  by ULID (`address_key_type`) and carry geocoding columns
  (`address_geocoding`) — all three are off by default, and the package itself
  never calls a geocoding service.
