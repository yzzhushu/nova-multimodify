<?php

namespace Jshxl\MultiModify;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\SupportsDependentFields;

class MultiModify extends Field
{
    use SupportsDependentFields;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'multi-modify';

    /**
     * 请求链接
     *
     * @var string
     * */
    public string $options;

    /**
     * 请求链接，POST请求
     * @param string $options
     *
     * @return self
     * */
    public function options(string $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * table显示的字段
     *
     * @var array
     * */
    public array $columns = [];

    /**
     * table显示的字段
     * @param array $columns
     *
     * @return self
     * */
    public function columns(array $columns = []): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * table是否显示索引
     *
     * @var bool
     * */
    public bool $withIndex = false;

    /**
     * table是否显示索引
     * @param bool $boolean
     *
     * @return self
     * */
    public function withIndex(bool $boolean = true): self
    {
        $this->withIndex = $boolean;
        return $this;
    }

    /**
     * 序号label
     *
     * @var string
     * */
    public string $indexName = 'index';

    /**
     * 设置序号label
     * @param string $indexName
     *
     * @return self
     * */
    public function indexName(string $indexName = 'index'): self
    {
        $this->indexName = $indexName;
        return $this;
    }

    /**
     * 序号宽度
     *
     * @var int
     * */
    public int $indexWidth = 10;

    /**
     * 设置序号宽度
     * @param int $indexWidth
     *
     * @return self
     * */
    public function indexWidth(int $indexWidth = 10): self
    {
        $this->indexWidth = $indexWidth;
        return $this;
    }

    /**
     * 选择文件按钮名称
     *
     * @var string
     * */
    public string $uploadButton = '点我选择文件';

    /**
     * 设置上传按钮名称
     * @param string $name
     *
     * @return self
     * */
    public function uploadButton(string $name = '点我选择文件'): self
    {
        $this->uploadButton = $name;
        return $this;
    }

    /**
     * 清除已选文件按钮名称
     *
     * @var string
     * */
    public string $removeButton = '清除已选文件';

    /**
     * 设置删除按钮名称
     * @param string $name
     *
     * @return self
     * */
    public function removeButton(string $name = '清除已选文件'): self
    {
        $this->removeButton = $name;
        return $this;
    }

    /**
     * 文件上传最大大小
     *
     * @var int
     * */
    public int $maxSize = 1024 * 1024 * 4;

    /**
     * 文件上传最大大小
     * @param int $maxSize
     *
     * @return self
     * */
    public function maxSize(int $maxSize): self
    {
        $this->maxSize = max(0, $maxSize);
        return $this;
    }

    /**
     * 是否直接保存原始数据
     *
     * @var string
     * */
    public string $saveType = 'data';

    /**
     * 数据保存形式
     * @param string $saveType
     *
     * @return self
     * */
    public function saveType(string $saveType = 'data'): self
    {
        $this->saveType = $saveType;
        return $this;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param Model $model
     * @param string $attribute
     *
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        if ($request->exists($requestAttribute)) {
            $value = json_decode($request->input($requestAttribute), true);
            $model->forceFill([Str::replace('.', '->', $attribute) => $value]);
        }
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function jsonSerialize(): array
    {
        if (!isset($this->options)) {
            throw new \Exception(__('MultiModify\'s option is required'));
        }

        return with(app(NovaRequest::class), function ($request) {
            return array_merge([
                'options'   => $this->options,
                'columns'   => $this->columns,
                'withIndex' => $this->withIndex,
                'maxSize'   => $this->maxSize,
                'saveType'  => $this->saveType,
                'tableIndexName'  => $this->indexName,
                'tableIndexWidth' => $this->indexWidth,
                'uploadButton'    => $this->uploadButton,
                'removeButton'    => $this->removeButton,
            ], parent::jsonSerialize());
        });
    }
}
