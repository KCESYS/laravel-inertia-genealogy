# Laravel Inertia Genealogy

A Laravel bridge for [kcesys/php-genealogy](https://github.com/KCESYS/php-genealogy) designed for seamless integration with Inertia.js.

## Installation

```bash
composer require kcesys/laravel-inertia-genealogy
```

## Features

- **Fluent Helper**: `Genealogy::for($data)` wrapper.
- **Inertia Integration**: `share()` method to automatically inject props.
- **Auto-Discovery**: Automatically transforms Eloquent Models.

## Usage

In your Controller:

```php
use KCESYS\LaravelGenealogy\Genealogy;
use App\Models\User;
use Inertia\Inertia;

public function index()
{
    $family = User::with('parents', 'children')->get();

    // Option 1: Pass as Prop
    return Inertia::render('MyTree', [
        'graph' => Genealogy::for($family)->toGraph([
             'label' => 'full_name', // Map 'label' to 'full_name' attribute
             'spouses' => fn($u) => $u->partners->pluck('id') // Custom relationship mapping
        ])
    ]);
}
```

### Sharing Globally

If you want the genealogy data available on every page (e.g. for a sidebar widget):

```php
// In HandleInertiaRequests Middleware
public function share(Request $request)
{
    return array_merge(parent::share($request), [
        'genealogy' => fn() => Genealogy::for($request->user()->family)->toGraph()
    ]);
}
```
