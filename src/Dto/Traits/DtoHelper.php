<?php

namespace EscolaLms\Webinar\Dto\Traits;

use Illuminate\Support\Str;

trait DtoHelper
{
    protected array $relations = [];
    protected array $files = [];

    protected function setterByData(array $data): void
    {
        foreach ($data as $k => $v) {
            $key = preg_replace_callback('/[_|-]([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $k);
            if (method_exists($this, 'set' . $key)) {
                $this->{'set' . $key}($v);
            } else {
                $key = lcfirst($key);
                $this->$key = $v;
            }
        }
    }

    protected function getterByAttribute(string $attribute)
    {
        $key = Str::studly($attribute);
        if (method_exists($this, 'set' . $key)) {
            return $this->{'get' . $key}();
        }
        return $this->{lcfirst($key)} ?? null;
    }

    protected function fillInArray(array $fillables): array
    {
        $result = [];
        foreach ($fillables as $fill) {
            $result[$fill] = $this->getterByAttribute($fill);
        }
        return $result;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

}
