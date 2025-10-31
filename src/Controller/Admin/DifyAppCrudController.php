<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\DifyCoreBundle\Entity\DifyApp;

/** @extends AbstractCrudController<DifyApp> */
#[AdminCrud(routePath: '/dify-core/dify-app', routeName: 'dify_core_dify_app')]
final class DifyAppCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DifyApp::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Dify 应用')
            ->setEntityLabelInPlural('Dify 应用管理')
            ->setDefaultSort(['valid' => 'DESC', 'createTime' => 'DESC'])
            ->setSearchFields(['name', 'baseUrl', 'description'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),

            TextField::new('name', '应用名称')
                ->setHelp('Dify应用的显示名称')
                ->setRequired(true),

            TextareaField::new('description', '应用描述')
                ->setHelp('应用的详细描述信息')
                ->setNumOfRows(3)
                ->hideOnIndex(),

            TextField::new('apiKey', 'API Key')
                ->setHelp('Dify应用的API密钥，用于API调用认证')
                ->hideOnIndex()
                ->setFormTypeOption('attr', ['autocomplete' => 'off'])
                ->setRequired(true),

            UrlField::new('baseUrl', 'API基础URL')
                ->setHelp('Dify API的基础地址，例如：https://api.dify.ai')
                ->setRequired(true),

            TextareaField::new('iframeCode', 'iframe嵌入代码')
                ->setHelp('Dify聊天窗口的iframe嵌入代码')
                ->hideOnIndex()
                ->setNumOfRows(5),

            BooleanField::new('valid', '是否有效')
                ->setHelp('控制应用是否可用')
                ->renderAsSwitch(),

            DateTimeField::new('createTime', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),

            DateTimeField::new('updatedAt', '更新时间')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '应用名称'))
            ->add(TextFilter::new('baseUrl', 'API基础URL'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
