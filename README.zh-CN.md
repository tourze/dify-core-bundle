# Dify 核心包

[English](README.md) | [中文](README.zh-CN.md)

用于 Dify AI 集成的核心 Symfony Bundle，提供应用配置管理、API 认证和通用数据传输对象。

## 功能特性

- **应用配置管理**：在数据库中存储和管理 Dify 应用设置
- **API 认证**：集中管理 API 密钥和端点
- **通用数据传输对象**：共享的数据传输对象和值对象
- **实体管理**：Dify 应用的核心实体
- **服务基础**：其他 Dify bundle 的基础服务和接口

## 安装

```bash
composer require tourze/dify-core-bundle
```

## 配置

将 bundle 添加到 `config/bundles.php`：

```php
return [
    // ... 其他 bundles
    Tourze\DifyCoreBundle\DifyCoreBundle::class => ['all' => true],
];
```

## 使用方法

此 bundle 为其他 Dify bundle 提供基础功能，通常作为依赖项使用，而不是直接使用。

## 系统要求

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+

## 许可证

此 bundle 基于 MIT 许可证发布。详细信息请查看 [LICENSE](LICENSE) 文件。