<?php

namespace Wbe\Crud\Controllers\Rapyd;

use Wbe\Crud\Models\Rapyd\FieldsProcessor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Wbe\Crud\Models\ContentTypes\ContentType;
use Wbe\Crud\Models\ContentTypes\ContentTypeFields;

use Zofe\Rapyd\DataFilter\DataFilter;
use Zofe\Rapyd\DataGrid\DataGrid;


class GridController extends Controller
{
    public function index($content_type)
    {

        /*$filter = DataFilter::source(Hints::query());
        $filter->add('market_option','market_option','text');
        $filter->add('outcome','outcome','text');
        $filter->add('value','value','text');
        $filter->add('hint','hint','text');
        $filter->submit('Знайти');
        $filter->reset('Очистити');
        $filter->build();

        $grid = DataGrid::source($filter);
        $grid->attributes(array("class"=>"table table-striped"));
        $grid->add('id','ID', true)->style("width:70px");
        $grid->add('market_option','market_option', 'market_option');
        $grid->add('outcome','outcome','outcome');
        $grid->add('value','value','value');
        $grid->add('hint|strip_tags|mb_substr[0,50]','hint','hint');
        $grid->edit(url('/admin/crud/' . $content_type . '/'), 'Edit','modify|delete');
        $grid->paginate(10);

        return view('crud::crud.grid', compact('filter', 'grid'));*/


        $content = ContentType::find($content_type);

        if (!$content) abort('500', 'Content type #' . $content_type . ' not found!');

        // from crud_content_type_fields
        $ct_fields = ContentTypeFields::getFieldsFromDB($content_type, [['grid_show', '=', \DB::raw(1)]]);


        $fields_schema = \Schema::getColumnListing($content->table);
        $fields_desc_schema = \Schema::getColumnListing($content->table . '_description');


        $fields = [];

        foreach ($fields_schema as $field) {
            if (isset($ct_fields[$field])) {
                $fields[$field] = $ct_fields[$field];
            }
        }

        foreach ($fields_desc_schema as $field) {
            if (isset($ct_fields[$field])) {
                $fields[$field] = $ct_fields[$field];
            }
        }

        $content_type_model = 'App\Models\\' . $content->model;
        $new_content_type_model = new $content_type_model;

        /*if (in_array('App\Models\Crud\Translatable', class_uses($content_type_model))) {
            $new_content_type_model = $new_content_type_model::translate(session('admin_lang_id'));
        }*/

        $filter = DataFilter::source($new_content_type_model);

        /*foreach ($fields as $field) {
            if ($field->grid_filter) {
                $display = $field->grid_custom_display ? $field->grid_custom_display : $field->name;
                if (in_array($field->type, ['textarea','redactor']))
                    $field_type = 'text';
                else
                    $field_type = $field->type;

                $f = $filter->add($field->name, $field->caption ? $field->caption : $display, $field_type);
                if ($field->form_attributes)
                    eval($field->form_attributes);
            }
        }*/

        FieldsProcessor::addFields($content, $filter, 'filter');

        $filter->submit('Знайти');
        $filter->reset('Очистити');
        $filter->build();


        $grid = DataGrid::source($filter);
        $grid->attributes(array("class" => "table table-striped"));

        //property_exists($new_content_type_model, 'primaryKey')

        //$primary_key = $new_content_type_model->primaryKey ? $new_content_type_model->primaryKey : 'id';
        //$grid->orderBy($primary_key);

        foreach ($fields as $field) {
            if ($field->grid_show && ($field->name != 'lang_id') && ($field->name != 'content_id')) {
                $display = $field->grid_custom_display ? $field->grid_custom_display : $field->name;
                $f = $grid->add($display, $field->caption ? $field->caption : $field->name, $field->name);

                if ($field->grid_attributes)
                    eval($field->grid_attributes);
            }
        }

        $grid->link(url('admin/fields_descriptor/content/' . $content_type), "Редагувати поля", "TR");
        $grid->link(url('/admin/crud/edit/' . $content_type . '?insert=1'), "Додати", "TR");

        $grid->edit(url('/admin/crud/edit/' . $content_type . '/'), 'Edit', 'modify|delete');
        $grid->paginate(10);

        return view('crud::crud.grid', compact('filter', 'grid'));
    }
}