<div class="row mg-tb-30">
    @foreach(LaravelLocalization::getSupportedLocales() as $locale => $properties)
        <div class="col-md-6">
            <div class="form-group" id="{{ $locale }}_name_div">
                @php $name[$locale] = isset($form_data) ? $form_data->getTranslation('name',$locale,false) : ""; @endphp
                <label
                    for="{{ $locale }}_name_input">@lang('site.' . $locale . '.name')</label>
                <input name="{{ $locale }}_name" type="text" value="{{ $name[$locale] }}"
                       class="form-control" id="{{ $locale }}_name_input"
                       placeholder="@lang('site.' . $locale . '.name')">
                <span id="{{ $locale }}_name_error" class="help-block"></span>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="is_required_input">@lang('site.is_required')</label>
            <input type="checkbox" name="is_required" id="is_required_input" value="1">
        </div>
    </div>
</div>