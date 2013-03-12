<?php

namespace FSC\HateoasBundle\Serializer;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class XmlFormViewSerializer
{
    protected static $baseTypes = array(
        'text', 'textarea', 'email', 'integer', 'money', 'number', 'password', 'percent', 'search', 'url', 'hidden',
        'collection', 'choice', 'checkbox', 'radio', 'datetime', 'date',
    );

    /**
     * @param FormView    $formView
     * @param \DOMElement $formElement
     */
    public function serialize(FormView $formView, \DOMElement $formElement)
    {
        $this->serializeBlock($formElement, $formView, 'rest');

        if ($formView->vars['multipart']) {
            $formElement->setAttribute('enctype', 'multipart/form-data');
        }

        if (isset($formView->vars['attr'])) {
            foreach ($formView->vars['attr'] as $name => $value) {
                $formElement->setAttribute($name, $value);
            }
        }
    }

    protected function serializeBlock(\DOMElement $parentElement, FormView $view, $blockName)
    {
        $variables = $view->vars;

        $type = null;
        foreach ($variables['block_prefixes'] as $blockPrefix) {
            if (in_array($blockPrefix, static::$baseTypes)) {
                $type = $blockPrefix; // We use the last found
            }
        }

        if ($view->isRendered()) {
            return;
        }

        if ('rest' == $blockName) {
            $this->serializeRestWidget($parentElement, $view, $variables);
        } else {
            switch ($type) {
                case 'text':
                    $this->serializeWidgetSimple($parentElement, $view, $variables);
                    break;
                case 'textarea':
                    $this->serializeTextareaWidget($parentElement, $view, $variables);
                    break;
                case 'email':
                    $this->serializeEmailWidget($parentElement, $view, $variables);
                    break;
                case 'integer':
                    $this->serializeIntegerWidget($parentElement, $view, $variables);
                    break;
                case 'number':
                    $this->serializeNumberWidget($parentElement, $view, $variables);
                    break;
                case 'password':
                    $this->serializePasswordWidget($parentElement, $view, $variables);
                    break;
                case 'percent':
                    $this->serializePercentWidget($parentElement, $view, $variables);
                    break;
                case 'search':
                    $this->serializeSearchWidget($parentElement, $view, $variables);
                    break;
                case 'url':
                    $this->serializeUrlWidget($parentElement, $view, $variables);
                    break;
                case 'choice':
                    $this->serializeChoiceWidget($parentElement, $view, $variables);
                    break;
                case 'hidden':
                    $this->serializeHiddenWidget($parentElement, $view, $variables);
                    break;
                case 'collection':
                    $this->serializeCollectionWidget($parentElement, $view, $variables);
                    break;
                case 'checkbox':
                    $this->serializeCheckboxWidget($parentElement, $view, $variables);
                    break;
                case 'radio':
                    $this->serializeRadioWidget($parentElement, $view, $variables);
                    break;
                case 'datetime':
                    $this->serializeDatetimeWidget($parentElement, $view, $variables);
                    break;
                case 'date':
                    $this->serializeDateWidget($parentElement, $view, $variables);
                    break;
                default:
                    switch ($blockName) {
                        case 'widget':
                            $this->serializeFormWidget($parentElement, $view, $variables);
                            break;
                        case 'row':
                            $this->serializeFormRow($parentElement, $view, $variables);
                            break;
                        default:
                            throw new \RuntimeException(__METHOD__.' Oups '.$view->vars['name'].' // '.$blockName);
                    }
            }


        }

        $view->setRendered();
    }

    /*
        {% for child in form %}
            {% if not child.rendered %}
                {{ form_row(child) }}
            {% endif %}
        {% endfor %}
    */
    protected function serializeRestWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        foreach ($view->children as $childView) {
            if (!$childView->isRendered()) {
                $this->serializeBlock($parentElement, $childView, 'row');
            }
        }
    }

    /*
        <div>
            {{ form_label(form) }}
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
    */
    protected function serializeFormRow(\DOMElement $parentElement, FormView $view, $variables)
    {
        // TODO handle labels and errors

        $this->serializeBlock($parentElement, $view, 'widget');
    }

    /*
        {% if compound %}
            {{ block('form_widget_compound') }}
        {% else %}
            {{ block('form_widget_simple') }}
        {% endif %}
    */
    protected function serializeFormWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        return $variables['compound']
            ? $this->serializeWidgetCompound($parentElement, $view, $variables)
            : $this->serializeWidgetSimple($parentElement, $view, $variables)
        ;
    }

    /*
        {% set type = type|default('text') %}
        <input type="{{ type }}" {{ block('widget_attributes') }} {% if value is not empty %}value="{{ value }}" {% endif %}/>
    */
    protected function serializeWidgetSimple(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'text';

        $inputElement = $parentElement->ownerDocument->createElement('input');
        $parentElement->appendChild($inputElement);

        $inputElement->setAttribute('type', $variables['type']);

        if (!empty($variables['value'])) {
            $inputElement->setAttribute('value', $variables['value']);
        }

        $this->addWidgetAttributes($inputElement, $view, $variables);
    }

    /*
        id="{{ id }}"
        name="{{ full_name }}"
        {% if read_only %} readonly="readonly"{% endif %}
        {% if disabled %} disabled="disabled"{% endif %}
        {% if required %} required="required"{% endif %}
        {% if max_length %} maxlength="{{ max_length }}"{% endif %}
        {% if pattern %} pattern="{{ pattern }}"{% endif %}
        {% for attrname, attrvalue in attr %}
            {% if attrname in ['placeholder', 'title'] %}
                {{ attrname }}="{{ attrvalue|trans({}, translation_domain) }}"
            {% else %}
                {{ attrname }}="{{ attrvalue }}"
            {% endif %}
        {% endfor %}
    */
    protected function addWidgetAttributes(\DOMElement $widgetElement, FormView $view, $variables)
    {
        $widgetElement->setAttribute('name', $variables['full_name']);

        if ($variables['read_only']) {
            $widgetElement->setAttribute('readonly', 'readonly');
        }

        if ($variables['disabled']) {
            $widgetElement->setAttribute('disabled', 'disabled');
        }

        if ($variables['required']) {
            $widgetElement->setAttribute('required', 'required');
        }

        if ($variables['max_length']) {
            $widgetElement->setAttribute('maxlength', $variables['max_length']);
        }

        if ($variables['pattern']) {
            $widgetElement->setAttribute('pattern', $variables['pattern']);
        }

        foreach ($variables['attr'] as $name => $value) {
            if (in_array($name, array('placeholder', 'title'))) {
                // TODO Translate the thing ...
            }

            $widgetElement->setAttribute($name, $value);
        }
    }

    /*
        <textarea {{ block('widget_attributes') }}>{{ value }}</textarea>
    */
    protected function serializeTextareaWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $textareaElement = $parentElement->ownerDocument->createElement('textarea', $variables['value']);
        $parentElement->appendChild($textareaElement);

        $this->addWidgetAttributes($textareaElement, $view, $variables);
    }

    /*
        {% set type = type|default('email') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializeEmailWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'email';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {% set type = type|default('number') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializeIntegerWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'integer';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {# type="number" doesn't work with floats #}
        {% set type = type|default('text') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializeNumberWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'number';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {% set type = type|default('password') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializePasswordWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'password';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {% set type = type|default('text') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializePercentWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'text';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {% set type = type|default('search') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializeSearchWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'search';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {% set type = type|default('url') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializeUrlWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'url';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        {% if expanded %}
            {{ block('choice_widget_expanded') }}
        {% else %}
            {{ block('choice_widget_collapsed') }}
        {% endif %}
    */
    protected function serializeChoiceWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        return isset($variables['expanded']) && $variables['expanded']
            ? $this->serializeChoiceWidgetExpanded($parentElement, $view, $variables)
            : $this->serializeChoiceWidgetCollapsed($parentElement, $view, $variables)
        ;
    }

    /*
        <div {{ block('widget_container_attributes') }}>
        {% for child in form %}
            {{ form_widget(child) }}
            {{ form_label(child) }}
        {% endfor %}
        </div>
    */
    protected function serializeChoiceWidgetExpanded(\DOMElement $parentElement, FormView $view, $variables)
    {
        foreach ($variables['form'] as $childView) {
            $this->serializeBlock($parentElement, $childView, 'widget');
        }
    }

    /*
        <select {{ block('widget_attributes') }}{% if multiple %} multiple="multiple"{% endif %}>
            {% if empty_value is not none %}
                <option value="">{{ empty_value|trans({}, translation_domain) }}</option>
            {% endif %}
            {% if preferred_choices|length > 0 %}
                {% set options = preferred_choices %}
                {{ block('choice_widget_options') }}
                {% if choices|length > 0 and separator is not none %}
                    <option disabled="disabled">{{ separator }}</option>
                {% endif %}
            {% endif %}
            {% set options = choices %}
            {{ block('choice_widget_options') }}
        </select>
    */
    protected function serializeChoiceWidgetCollapsed(\DOMElement $parentElement, FormView $view, $variables)
    {
        $selectElement = $parentElement->ownerDocument->createElement('select');
        $parentElement->appendChild($selectElement);

        $this->addWidgetAttributes($selectElement, $view, $variables);

        if (isset($variables['multiple']) && $variables['multiple']) {
            $selectElement->setAttribute('multiple', 'multiple');
        }

        if (isset($variables['empty_value']) && null !== $variables['empty_value']) {
            $noneOptionElement = $selectElement->ownerDocument->createElement('option', $variables['empty_value']);
            $noneOptionElement->setAttribute('value', '');

            $selectElement->appendChild($noneOptionElement);
        }

        if (isset($variables['preferred_choices']) && 0 < count($variables['preferred_choices'])) {
            $variables['options'] = $variables['preferred_choices'];
            $this->serializeChoiceWidgetOptions($selectElement, $view, $variables);

            if (0 < count($variables['choices']) && null !== $variables['separator']) {
                $separatorOptionElement = $selectElement->ownerDocument->createElement('option', $variables['separator']);
                $separatorOptionElement->setAttribute('disabled', 'disabled');

                $selectElement->appendChild($separatorOptionElement);
            }
        }

        $variables['options'] = $variables['choices'];
        $this->serializeChoiceWidgetOptions($selectElement, $view, $variables);
    }

    /*
        {% for group_label, choice in options %}
            {% if choice is iterable %}
                <optgroup label="{{ group_label|trans({}, translation_domain) }}">
                    {% set options = choice %}
                    {{ block('choice_widget_options') }}
                </optgroup>
            {% else %}
                <option value="{{ choice.value }}"{% if choice is selectedchoice(value) %} selected="selected"{% endif %}>{{ choice.label|trans({}, translation_domain) }}</option>
            {% endif %}
        {% endfor %}
    */
    protected function serializeChoiceWidgetOptions(\DOMElement $selectElement, FormView $view, $variables)
    {
        foreach ($variables['options'] as $groupLabel => $choiceView) {
            if (is_array($choiceView) || $choiceView instanceof \Traversable) {
                $this->serializeChoiceWidgetOptions($selectElement, $view, array_merge($variables, array(
                    'options' => $choiceView,
                )));
            } else {
                $optionElement = $selectElement->ownerDocument->createElement('option', $choiceView->label);
                $optionElement->setAttribute('value', $choiceView->value);

                if ($this->isSelectedChoice($choiceView, $variables['value'])) {
                    $optionElement->setAttribute('selected', 'selected');
                }

                $selectElement->appendChild($optionElement);
            }
        }
    }

    /*
        {% set type = type|default('hidden') %}
        {{ block('form_widget_simple') }}
    */
    protected function serializeHiddenWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $variables['type'] = isset($variables['type']) ? $variables['type'] : 'hidden';

        $this->serializeWidgetSimple($parentElement, $view, $variables);
    }

    /*
        <div {{ block('widget_container_attributes') }}>
            {% if form.parent is empty %}
                {{ form_errors(form) }}
            {% endif %}
            {{ block('form_rows') }}
            {{ form_rest(form) }}
        </div>
    */
    protected function serializeWidgetCompound(\DOMElement $parentElement, FormView $view, $variables)
    {
        $this->serializeFormRows($parentElement, $view, $variables);

        $this->serializeBlock($parentElement, $variables['form'], 'rest');
    }

    /*
         {% for child in form %}
             {{ form_row(child) }}
         {% endfor %}
    */
    protected function serializeFormRows(\DOMElement $parentElement, FormView $view, $variables)
    {
        foreach ($variables['form'] as $child) {
            $this->serializeBlock($parentElement, $child, 'row');
        }
    }

    /*
        {% if prototype is defined %}
            {% set attr = attr|merge({'data-prototype': form_row(prototype) }) %}
        {% endif %}
        {{ block('form_widget') }}
    */
    protected function serializeCollectionWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        if (isset($variables['prototype'])) {
            $this->serializePrototype($parentElement, $variables);
        } else {
            $this->serializeFormWidget($parentElement, $view, $variables);
        }
    }

    protected function serializePrototype(\DOMElement $parentElement, $variables)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $divElement = $document->createElement('div');
        $divElement->setAttribute('id',$variables['id'].'___name__');

        $this->serializeBlock($divElement,$variables['prototype'], 'row');

        $ulElement = $parentElement->ownerDocument->createElement('ul');
        $ulElement->setAttribute('id',$variables['id']);
        $ulElement->setAttribute('data-prototype', htmlentities($document->saveHTML($divElement), ENT_QUOTES, 'UTF-8'));

        $parentElement->appendChild($ulElement);
    }

    /*
        <input type="checkbox" {{ block('widget_attributes') }}{% if value is defined %} value="{{ value }}"{% endif %}{% if checked %} checked="checked"{% endif %} />
    */
    protected function serializeCheckboxWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $inputElement = $parentElement->ownerDocument->createElement('input');
        $inputElement->setAttribute('type', 'checkbox');

        if (isset($variables['value'])) {
            $inputElement->setAttribute('value', $variables['value']);
        }

        if (isset($variables['checked']) && $variables['checked']) {
            $inputElement->setAttribute('checked', $variables['checked']);
        }

        $this->addWidgetAttributes($inputElement, $view, $variables);

        $parentElement->appendChild($inputElement);
    }

    /*
        <input type="radio" {{ block('widget_attributes') }}{% if value is defined %} value="{{ value }}"{% endif %}{% if checked %} checked="checked"{% endif %} />
    */
    protected function serializeRadioWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        $inputElement = $parentElement->ownerDocument->createElement('input');
        $inputElement->setAttribute('type', 'radio');

        if (isset($variables['value'])) {
            $inputElement->setAttribute('value', $variables['value']);
        }

        if (isset($variables['checked']) && $variables['checked']) {
            $inputElement->setAttribute('checked', $variables['checked']);
        }

        $this->addWidgetAttributes($inputElement, $view, $variables);

        $parentElement->appendChild($inputElement);
    }

    /*
        {% if widget == 'single_text' %}
            {{ block('form_widget_simple') }}
        {% else %}
            <div {{ block('widget_container_attributes') }}>
                {{ form_errors(form.date) }}
                {{ form_errors(form.time) }}
                {{ form_widget(form.date) }}
                {{ form_widget(form.time) }}
            </div>
        {% endif %}
    */
    protected function serializeDatetimeWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        if ('single_text' == $variables['widget']) {
            $this->serializeWidgetSimple($parentElement, $view, $variables);
        } else {
            $this->serializeBlock($parentElement, $view->children['date'], 'widget');
            $this->serializeBlock($parentElement, $view->children['time'], 'widget');
        }
    }

    /*
        {% if widget == 'single_text' %}
            {{ block('form_widget_simple') }}
        {% else %}
            <div {{ block('widget_container_attributes') }}>
                {{ date_pattern|replace({
                    '{{ year }}':  form_widget(form.year),
                    '{{ month }}': form_widget(form.month),
                    '{{ day }}':   form_widget(form.day),
                })|raw }}
            </div>
        {% endif %}
    */
    protected function serializeDateWidget(\DOMElement $parentElement, FormView $view, $variables)
    {
        if ('single_text' == $variables['widget']) {
            $this->serializeWidgetSimple($parentElement, $view, $variables);
        } else {
            // TODO handle order
            $this->serializeBlock($parentElement, $view->children['year'], 'widget');
            $this->serializeBlock($parentElement, $view->children['month'], 'widget');
            $this->serializeBlock($parentElement, $view->children['day'], 'widget');
        }
    }

    /**
     * Copied from the symfony src code
     *
     * @see Symfony\Bridge\Twig\Extension\FormExtension::isSelectedChoice
     */
    protected function isSelectedChoice(ChoiceView $choice, $selectedValue)
    {
        if (is_array($selectedValue)) {
            return false !== array_search($choice->value, $selectedValue, true);
        }

        return $choice->value === $selectedValue;
    }
}
