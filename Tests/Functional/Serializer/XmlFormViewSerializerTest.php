<?php

namespace FSC\HateoasBundle\Tests\Functional\Serializer;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Serializer\XmlFormViewSerializer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * @group functional
 */
class XmlFormViewSerializerTest extends TestCase
{
    public function test()
    {
        $formFactory = $this->getKernel()->getContainer()->get('form.factory');
        $form = $formFactory->createBuilder('form')
            ->add('name', 'text')
            ->add('description', 'textarea')
            ->add('email', 'email')
            ->add('age', 'integer')
            ->add('height', 'number')
            ->add('password', 'password')
            ->add('progress', 'percent')
            ->add('query', 'search')
            ->add('website', 'url')
            ->add('gender', 'choice', array(
                'choices' => array('m' => 'male', 'f' => 'female')
            ))
            ->add('genderRadio', 'choice', array(
                'choices' => array('m' => 'male', 'f' => 'female'),
                'expanded' => true,
                'multiple' => false,
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
                'label'     => 'Show this entry publicly?',
                'required'  => false,
            ))
            ->getForm();

        $form->setData(array(
            'towns' => array('par' => 'Paris', 'lon' => 'London'),
            'public' => true,
            'description' => 'Desc',
        ));

        $formView = $form->createView();

        $xmlFormViewSerializer = new XmlFormViewSerializer();

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form>
    <input type="text" name="form[name]" required="required"/>
    <textarea name="form[description]" required="required">Desc</textarea>
    <input type="email" name="form[email]" required="required"/>
    <input type="integer" name="form[age]" required="required"/>
    <input type="number" name="form[height]" required="required"/>
    <input type="password" name="form[password]" required="required"/>
    <input type="text" name="form[progress]" required="required"/>
    <input type="search" name="form[query]" required="required"/>
    <input type="url" name="form[website]" required="required"/>
    <select name="form[gender]" required="required">
        <option value="m">male</option>
        <option value="f">female</option>
    </select>
    <input type="radio" value="m" name="form[genderRadio]" required="required"/>
    <input type="radio" value="f" name="form[genderRadio]" required="required"/>
    <input type="hidden" name="form[limit]"/>
    <input type="email" value="Paris" name="form[towns][par]" class="email-box"/>
    <input type="email" value="London" name="form[towns][lon]" class="email-box"/>
    <input type="checkbox" value="1" checked="1" name="form[public]"/>
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

        $xmlFormViewSerializer = new XmlFormViewSerializer();

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form enctype="multipart/form-data">
    <input type="file" name="form[avatar]" required="required"/>
</form>
XML
            , $formElement);
    }

    public function testCollectionType()
    {
        $formFactory = $this->getKernel()->getContainer()->get('form.factory');

        $form = $formFactory->createBuilder('form')
            ->add('service', 'collection', array(
                'type'         => new availabilityFormType(),
                'allow_add' => true
            ))
            ->getForm();

        $formView = $form->createView();

        $xmlFormViewSerializer = new XmlFormViewSerializer();

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form>
    <ul id="form_service" data-prototype="&amp;lt;div id=&amp;quot;form_service___name__&amp;quot;&amp;gt;&#10;&amp;lt;input type=&amp;quot;text&amp;quot; name=&amp;quot;form[service][__name__][timeId]&amp;quot; required=&amp;quot;required&amp;quot;&amp;gt;&amp;lt;input type=&amp;quot;text&amp;quot; name=&amp;quot;form[service][__name__][dayId]&amp;quot; required=&amp;quot;required&amp;quot;&amp;gt;&#10;&amp;lt;/div&amp;gt;"/>
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

        $xmlFormViewSerializer = new XmlFormViewSerializer();

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form>
    <select name="form[publishedAt][year]" required="required">
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
    <select name="form[publishedAt][month]" required="required">
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
    <select name="form[publishedAt][day]" required="required">
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
    <input type="text" name="form[editedAt][year]" required="required"/>
    <input type="text" name="form[editedAt][month]" required="required"/>
    <input type="text" name="form[editedAt][day]" required="required"/>
    <input type="date" name="form[createdAt]" required="required"/>
    <select name="form[publishedAtTime][date][year]" required="required">
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
    <select name="form[publishedAtTime][date][month]" required="required">
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
    <select name="form[publishedAtTime][date][day]" required="required">
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
    <select name="form[publishedAtTime][time][hour]" required="required">
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
    <select name="form[publishedAtTime][time][minute]" required="required">
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

        $xmlFormViewSerializer = new XmlFormViewSerializer();

        $xmlFormViewSerializer->serialize($formView, $formElement = $this->createFormElement());

        $this->assertXmlElementEquals(<<<XML
<form method="POST" action="http://localhost/hey" rel="create">
    <input type="text" name="form[name]" required="required"/>
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

class availabilityFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('timeId');
        $builder->add('dayId');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }
    public function getName()
    {
        return 'availability';
    }

}