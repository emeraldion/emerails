<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
 */

require_once __DIR__ . '/../../../include/common.inc.php';
require_once __DIR__ . '/../base_test.php';

class CommonTest extends UnitTest
{
    public function test_pluralize()
    {
        $this->assertEquals('men', pluralize('man'));
        $this->assertEquals('women', pluralize('woman'));
        $this->assertEquals('dogs', pluralize('dog'));
        $this->assertEquals('puppies', pluralize('puppy'));
        $this->assertEquals('cats', pluralize('cat'));
        $this->assertEquals('cangaroos', pluralize('cangaroo'));
        $this->assertEquals('pigeons', pluralize('pigeon'));
        $this->assertEquals('laypeople', pluralize('layperson'));
        $this->assertEquals('businesswomen', pluralize('businesswoman'));
        $this->assertEquals('tomatoes', pluralize('tomato'));
        $this->assertEquals('wellies', pluralize('welly'));
        $this->assertEquals('babies', pluralize('baby'));
        $this->assertEquals('children', pluralize('child'));
        $this->assertEquals('grandchildren', pluralize('grandchild'));
        $this->assertEquals('zoos', pluralize('zoo'));
        $this->assertEquals('videos', pluralize('video'));
        $this->assertEquals('portfolios', pluralize('portfolio'));
    }

    public function test_singularize()
    {
        $this->assertEquals('man', singularize('men'));
        $this->assertEquals('woman', singularize('women'));
        $this->assertEquals('dog', singularize('dogs'));
        $this->assertEquals('puppy', singularize('puppies'));
        $this->assertEquals('cat', singularize('cats'));
        $this->assertEquals('cangaroo', singularize('cangaroos'));
        $this->assertEquals('pigeon', singularize('pigeons'));
        $this->assertEquals('layperson', singularize('laypeople'));
        $this->assertEquals('businesswoman', singularize('businesswomen'));
        $this->assertEquals('tomato', singularize('tomatoes'));
        $this->assertEquals('welly', singularize('wellies'));
        $this->assertEquals('baby', singularize('babies'));
        $this->assertEquals('child', singularize('children'));
        $this->assertEquals('grandchild', singularize('grandchildren'));
        $this->assertEquals('zoo', singularize('zoos'));
        $this->assertEquals('video', singularize('videos'));
        $this->assertEquals('portfolio', singularize('portfolios'));
    }

    public function test_ends_with()
    {
        $this->assertTrue(ends_with('woman', 'man'));
        $this->assertTrue(ends_with('grandchild', 'child'));
        $this->assertTrue(ends_with('pirateship', 'ship'));
    }

    public function test_class_name_to_table_name()
    {
        $this->assertEquals('business_women', class_name_to_table_name('BusinessWoman'));
        $this->assertEquals('lay_people', class_name_to_table_name('LayPerson'));
        $this->assertEquals('puppies', class_name_to_table_name('Puppy'));
        $this->assertEquals('tomatoes', class_name_to_table_name('Tomato'));
    }

    public function test_table_name_to_class_name()
    {
        $this->assertEquals('BusinessWoman', table_name_to_class_name('business_women'));
        $this->assertEquals('LayPerson', table_name_to_class_name('lay_people'));
        $this->assertEquals('Puppy', table_name_to_class_name('puppies'));
        $this->assertEquals('Tomato', table_name_to_class_name('tomatoes'));
    }

    public function test_joined_lower()
    {
        $this->assertEquals('some_random_text', joined_lower('some random text'));
        $this->assertEquals('random_text_with_chars_', joined_lower('random text with chars @#$%^&*()-+'));
    }

    public function test_joined_lower_to_camel_case()
    {
        $this->assertEquals('BusinessWoman', joined_lower_to_camel_case('business_woman'));
        $this->assertEquals('LayPerson', joined_lower_to_camel_case('lay_person'));
        $this->assertEquals('Puppy', joined_lower_to_camel_case('puppy'));
        $this->assertEquals('Tomato', joined_lower_to_camel_case('tomato'));
    }

    public function test_camel_case_to_joined_lower()
    {
        $this->assertEquals('business_woman', camel_case_to_joined_lower('BusinessWoman'));
        $this->assertEquals('lay_person', camel_case_to_joined_lower('LayPerson'));
        $this->assertEquals('puppy', camel_case_to_joined_lower('Puppy'));
        $this->assertEquals('tomato', camel_case_to_joined_lower('Tomato'));
    }

    public function test_class_name_to_foreign_key()
    {
        $this->assertEquals('business_woman_id', class_name_to_foreign_key('BusinessWoman'));
        $this->assertEquals('lay_person_id', class_name_to_foreign_key('LayPerson'));
        $this->assertEquals('puppy_id', class_name_to_foreign_key('Puppy'));
        $this->assertEquals('tomato_id', class_name_to_foreign_key('Tomato'));
    }

    public function test_table_name_to_foreign_key()
    {
        $this->assertEquals('business_woman_id', table_name_to_foreign_key('business_women'));
        $this->assertEquals('lay_person_id', table_name_to_foreign_key('lay_people'));
        $this->assertEquals('puppy_id', table_name_to_foreign_key('puppies'));
        $this->assertEquals('tomato_id', table_name_to_foreign_key('tomatoes'));
    }
}
?>
