<?php

declare(strict_types=1);

namespace App\Actions\Builder\Menu;

use App\Actions\Builder\MenuItem;
use Illuminate\Support\Facades\Gate;

class DataManagement extends Base
{
    public function build(): self
    {
        $this->setHeadingLabel(__('Data Management'))
            ->setHeadingIcon('database')
            ->setAuthorization('access.data-management');

        $menuItems = $this->createAndProcessMenuItems($this->getMenuConfiguration());
        $this->setMenus($menuItems);

        return $this;
    }

    /**
     * @return array<callable>
     */
    protected function getMenuConfiguration(): array
    {
        return [
            fn () => $this->createDataSourcesMenuItem(),
            fn () => $this->createApiSpecsMenuItem(),
        ];
    }

    private function createDataSourcesMenuItem(): MenuItem
    {
        return (new MenuItem)
            ->setLabel(__('Data Sources'))
            ->setUrl(route('data-sources.index'))
            ->setVisible(fn () => Gate::allows('manage.datasources'))
            ->setTooltip(__('Manage data source connections'))
            ->setDescription(__('Connect and introspect data sources'))
            ->setIcon('database');
    }

    private function createApiSpecsMenuItem(): MenuItem
    {
        return (new MenuItem)
            ->setLabel(__('API Specs'))
            ->setUrl(route('api-specs.index'))
            ->setVisible(fn () => Gate::allows('manage.specs'))
            ->setTooltip(__('View generated API specs'))
            ->setDescription(__('Review and manage OpenAPI specs'))
            ->setIcon('file-text');
    }
}
