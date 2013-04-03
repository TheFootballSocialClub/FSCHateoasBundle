<?php

namespace FSC\HateoasBundle\Tests\Functional\Serializer;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Serializer\XmlFormViewSerializer;

/**
 * @group functional
 */
class XmlFormViewSerializerTest extends TestCase
{
    public function test()
    {
        $formFactory = $this->getKernel()->getContainer()->get('form.factory');
        $form = $formFactory->createBuilder('form')
            ->add('name', 'text',array(
                'label' => 'custom label'
            ))
            ->add('description', 'textarea')
            ->add('email', 'email')
            ->add('age', 'integer')
            ->add('height', 'number')
            ->add('password', 'password')
            ->add('progress', 'percent')
            ->add('query', 'search')
            ->add('website', 'url')
            ->add('gender', 'choice', array(
                'choices' => array('m' => 'male', 'f' => 'female'),
                'label' => 'custom'
            ))
            ->add('genderRadio', 'choice', array(
                'choices' => array('m' => 'male', 'f' => 'female'),
                'expanded' => true,
                'multiple' => false,
                'label' => 'label'
            ))
            ->add('limit', 'hidden')
            ->add('towns', 'collection', array(
                // each item in the array will be an "email" field
                'type'   => 'email',
                // these options are passed to each "email" type
                'options'  => array(
                    'required'  => false,
                    'attr'      => array('class' => 'email-box')
                ),
            ))
            ->add('public', 'checkbox', array(
                'label'     => 'label',
                'required'  => false,
            ))
            ->getForm();

        $form->setData(array(
            'towns' => array('par' => 'Paris', 'lon' => 'London'),
            'public' => true,
            'description' => 'Desc',
        ));

        $formView = $form->createView();

        $translator = $this->getKernel()->getContainer()->get('translator');
        $xmlFormViewSerializer = new XmlFormViewSerializer($translator);

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form>
    <label for="form_name">custom label</label>
    <input type="text" id="form_name" name="form[name]" required="required"/>
    <label for="form_description">Description</label>
    <textarea id="form_description" name="form[description]" required="required">Desc</textarea>
    <label for="form_email">Email</label>
    <input type="email" id="form_email" name="form[email]" required="required"/>
    <label for="form_age">Age</label>
    <input type="integer" id="form_age" name="form[age]" required="required"/>
    <label for="form_height">Height</label>
    <input type="number" id="form_height" name="form[height]" required="required"/>
    <label for="form_password">Password</label>
    <input type="password" id="form_password" name="form[password]" required="required"/>
    <label for="form_progress">Progress</label>
    <input type="text" id="form_progress" name="form[progress]" required="required"/>
    <label for="form_query">Query</label>
    <input type="search" id="form_query" name="form[query]" required="required"/>
    <label for="form_website">Website</label>
    <input type="url" id="form_website" name="form[website]" required="required"/>
    <label for="form_gender">custom</label>
    <select name="form[gender]" required="required" id="form_gender">
        <option value="m">male</option>
        <option value="f">female</option>
    </select>
    <label for="form_genderRadio">label</label>
    <fieldset id="form_genderRadio">
        <label for="form_genderRadio_0">male</label>
        <input type="radio" id="form_genderRadio_0" value="m" name="form[genderRadio]" required="required"/>
        <label for="form_genderRadio_1">female</label>
        <input type="radio" id="form_genderRadio_1" value="f" name="form[genderRadio]" required="required"/>
    </fieldset>
    <label for="form_limit">Limit</label>
    <input type="hidden" id="form_limit" name="form[limit]"/>
    <label for="form_towns">Towns</label>
    <label for="form_towns_par">Par</label>
    <input type="email" id="form_towns_par" value="Paris" name="form[towns][par]" class="email-box"/>
    <label for="form_towns_lon">Lon</label>
    <input type="email" id="form_towns_lon" value="London" name="form[towns][lon]" class="email-box"/>
    <label for="form_public">label</label>
    <input type="checkbox" id="form_public" value="1" checked="1" name="form[public]"/>
</form>
XML
        , $formElement);
    }

    public function testFileType()
    {
        $formFactory = $this->getKernel()->getContainer()->get('form.factory');
        $form = $formFactory->createBuilder('form')
            ->add('avatar', 'file')
            ->getForm();

        $formView = $form->createView();

        $translator = $this->getKernel()->getContainer()->get('translator');
        $xmlFormViewSerializer = new XmlFormViewSerializer($translator);

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form enctype="multipart/form-data">
    <label for="form_avatar">Avatar</label>
    <input type="file" id="form_avatar" name="form[avatar]" required="required"/>
</form>
XML
            , $formElement);
    }

    public function testDateFields()
    {
        // force locale for PHP_INTL DateTime
        locale_set_default('en-US');

        $formFactory = $this->getKernel()->getContainer()->get('form.factory');
        $form = $formFactory->createBuilder('form')
            ->add('publishedAt', 'date', array(
                'input'  => 'datetime',
                'widget' => 'choice',
            ))
            ->add('editedAt', 'date', array(
                'input'  => 'datetime',
                'widget' => 'text',
            ))
            ->add('createdAt', 'date', array(
                'input'  => 'datetime',
                'widget' => 'single_text',
            ))
            ->add('publishedAtTime', 'datetime', array(
                'input'  => 'datetime',
            ))
            ->getForm()
        ;

        $formView = $form->createView();

        $translator = $this->getKernel()->getContainer()->get('translator');
        $xmlFormViewSerializer = new XmlFormViewSerializer($translator);

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form>
    <label for="form_publishedAt">Publishedat</label>
    <label for="form_publishedAt_year">Year</label>
    <select name="form[publishedAt][year]" required="required" id="form_publishedAt_year">
        <option value="2008">2008</option>
        <option value="2009">2009</option>
        <option value="2010">2010</option>
        <option value="2011">2011</option>
        <option value="2012">2012</option>
        <option value="2013">2013</option>
        <option value="2014">2014</option>
        <option value="2015">2015</option>
        <option value="2016">2016</option>
        <option value="2017">2017</option>
        <option value="2018">2018</option>
    </select>
    <label for="form_publishedAt_month">Month</label>
    <select name="form[publishedAt][month]" required="required" id="form_publishedAt_month">
        <option value="1">Jan</option>
        <option value="2">Feb</option>
        <option value="3">Mar</option>
        <option value="4">Apr</option>
        <option value="5">May</option>
        <option value="6">Jun</option>
        <option value="7">Jul</option>
        <option value="8">Aug</option>
        <option value="9">Sep</option>
        <option value="10">Oct</option>
        <option value="11">Nov</option>
        <option value="12">Dec</option>
    </select>
    <label for="form_publishedAt_day">Day</label>
    <select name="form[publishedAt][day]" required="required" id="form_publishedAt_day">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
        <option value="15">15</option>
        <option value="16">16</option>
        <option value="17">17</option>
        <option value="18">18</option>
        <option value="19">19</option>
        <option value="20">20</option>
        <option value="21">21</option>
        <option value="22">22</option>
        <option value="23">23</option>
        <option value="24">24</option>
        <option value="25">25</option>
        <option value="26">26</option>
        <option value="27">27</option>
        <option value="28">28</option>
        <option value="29">29</option>
        <option value="30">30</option>
        <option value="31">31</option>
    </select>
    <label for="form_editedAt">Editedat</label>
    <label for="form_editedAt_year">Year</label>
    <input type="text" id="form_editedAt_year" name="form[editedAt][year]" required="required"/>
    <label for="form_editedAt_month">Month</label>
    <input type="text" id="form_editedAt_month" name="form[editedAt][month]" required="required"/>
    <label for="form_editedAt_day">Day</label>
    <input type="text" id="form_editedAt_day" name="form[editedAt][day]" required="required"/>
    <label for="form_createdAt">Createdat</label>
    <input type="date" id="form_createdAt" name="form[createdAt]" required="required"/>
    <label for="form_publishedAtTime">Publishedattime</label>
    <label for="form_publishedAtTime_date">Date</label>
    <label for="form_publishedAtTime_date_year">Year</label>
    <select name="form[publishedAtTime][date][year]" required="required" id="form_publishedAtTime_date_year">
        <option value="2008">2008</option>
        <option value="2009">2009</option>
        <option value="2010">2010</option>
        <option value="2011">2011</option>
        <option value="2012">2012</option>
        <option value="2013">2013</option>
        <option value="2014">2014</option>
        <option value="2015">2015</option>
        <option value="2016">2016</option>
        <option value="2017">2017</option>
        <option value="2018">2018</option>
    </select>
    <label for="form_publishedAtTime_date_month">Month</label>
    <select name="form[publishedAtTime][date][month]" required="required" id="form_publishedAtTime_date_month">
        <option value="1">Jan</option>
        <option value="2">Feb</option>
        <option value="3">Mar</option>
        <option value="4">Apr</option>
        <option value="5">May</option>
        <option value="6">Jun</option>
        <option value="7">Jul</option>
        <option value="8">Aug</option>
        <option value="9">Sep</option>
        <option value="10">Oct</option>
        <option value="11">Nov</option>
        <option value="12">Dec</option>
    </select>
    <label for="form_publishedAtTime_date_day">Day</label>
    <select name="form[publishedAtTime][date][day]" required="required" id="form_publishedAtTime_date_day">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
        <option value="15">15</option>
        <option value="16">16</option>
        <option value="17">17</option>
        <option value="18">18</option>
        <option value="19">19</option>
        <option value="20">20</option>
        <option value="21">21</option>
        <option value="22">22</option>
        <option value="23">23</option>
        <option value="24">24</option>
        <option value="25">25</option>
        <option value="26">26</option>
        <option value="27">27</option>
        <option value="28">28</option>
        <option value="29">29</option>
        <option value="30">30</option>
        <option value="31">31</option>
    </select>
    <label for="form_publishedAtTime_time">Time</label>
    <label for="form_publishedAtTime_time_hour">Hour</label>
    <select name="form[publishedAtTime][time][hour]" required="required" id="form_publishedAtTime_time_hour">
        <option value="0">00</option>
        <option value="1">01</option>
        <option value="2">02</option>
        <option value="3">03</option>
        <option value="4">04</option>
        <option value="5">05</option>
        <option value="6">06</option>
        <option value="7">07</option>
        <option value="8">08</option>
        <option value="9">09</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
        <option value="15">15</option>
        <option value="16">16</option>
        <option value="17">17</option>
        <option value="18">18</option>
        <option value="19">19</option>
        <option value="20">20</option>
        <option value="21">21</option>
        <option value="22">22</option>
        <option value="23">23</option>
    </select>
    <label for="form_publishedAtTime_time_minute">Minute</label>
    <select name="form[publishedAtTime][time][minute]" required="required" id="form_publishedAtTime_time_minute">
        <option value="0">00</option>
        <option value="1">01</option>
        <option value="2">02</option>
        <option value="3">03</option>
        <option value="4">04</option>
        <option value="5">05</option>
        <option value="6">06</option>
        <option value="7">07</option>
        <option value="8">08</option>
        <option value="9">09</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
        <option value="15">15</option>
        <option value="16">16</option>
        <option value="17">17</option>
        <option value="18">18</option>
        <option value="19">19</option>
        <option value="20">20</option>
        <option value="21">21</option>
        <option value="22">22</option>
        <option value="23">23</option>
        <option value="24">24</option>
        <option value="25">25</option>
        <option value="26">26</option>
        <option value="27">27</option>
        <option value="28">28</option>
        <option value="29">29</option>
        <option value="30">30</option>
        <option value="31">31</option>
        <option value="32">32</option>
        <option value="33">33</option>
        <option value="34">34</option>
        <option value="35">35</option>
        <option value="36">36</option>
        <option value="37">37</option>
        <option value="38">38</option>
        <option value="39">39</option>
        <option value="40">40</option>
        <option value="41">41</option>
        <option value="42">42</option>
        <option value="43">43</option>
        <option value="44">44</option>
        <option value="45">45</option>
        <option value="46">46</option>
        <option value="47">47</option>
        <option value="48">48</option>
        <option value="49">49</option>
        <option value="50">50</option>
        <option value="51">51</option>
        <option value="52">52</option>
        <option value="53">53</option>
        <option value="54">54</option>
        <option value="55">55</option>
        <option value="56">56</option>
        <option value="57">57</option>
        <option value="58">58</option>
        <option value="59">59</option>
    </select>
</form>
XML
            , $formElement);
    }

    public function testAttributes()
    {
        $formFactory = $this->getKernel()->getContainer()->get('form.factory');
        $form = $formFactory
            ->createBuilder('form')
            ->add('name', 'text')
            ->getForm()
        ;

        $formView = $form->createView();

        $formView->vars['attr'] = array(
            'method' => 'POST',
            'action' => 'http://localhost/hey',
            'rel'    => 'create',
        );

        $translator = $this->getKernel()->getContainer()->get('translator');
        $xmlFormViewSerializer = new XmlFormViewSerializer($translator);

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form method="POST" action="http://localhost/hey" rel="create">
    <label for="form_name">Name</label>
    <input type="text" id="form_name" name="form[name]" required="required"/>
</form>
XML
            , $formElement);
    }

    protected function assertXmlElementEquals($expectedString, \DOMElement $element)
    {
        $elementString = $element->ownerDocument->saveXML($element);
        $elementString = preg_replace('/^[ ]+(?=<)/m','$0$0', $elementString); // Increase indentation to 4 :>

        $this->assertEquals($expectedString, $elementString);
    }

    protected function createFormElement()
    {
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        $domDocument->formatOutput = true;

        return $domDocument->createElement('form');
    }
}
