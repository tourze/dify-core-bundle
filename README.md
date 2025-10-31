# Dify Core Bundle

[English](README.md) | [中文](README.zh-CN.md)

Core Symfony Bundle for Dify AI integration providing application configuration management, API authentication, and common DTOs.

## Features

- **Application Configuration Management**: Store and manage Dify app settings in database
- **API Authentication**: Centralized API key and endpoint management
- **Common DTOs**: Shared data transfer objects and value objects
- **Entity Management**: Core entities for Dify applications
- **Service Foundation**: Base services and interfaces for other Dify bundles

## Installation

```bash
composer require tourze/dify-core-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ... other bundles
    Tourze\DifyCoreBundle\DifyCoreBundle::class => ['all' => true],
];
```

## Usage

This bundle provides the foundation for other Dify bundles. It's typically used as a dependency rather than directly.

## Requirements

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+

## License

This bundle is released under the MIT license. See the [LICENSE](LICENSE) file for details.