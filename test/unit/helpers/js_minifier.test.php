<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

require_once __DIR__ . '/../base_test.php';

use Emeraldion\EmeRails\Helpers\JSMinifier;

class JSMinifierTest extends UnitTest
{
    public function test_get_instance()
    {
        $this->assertNotNull(JSMinifier::get_instance());
    }

    public function test_minify()
    {
        // JS snippets from https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch

        $this->assertEquals(
            <<<EOT
            fetch('http://example.com/movies.json').then((response)=>response.json()).then((data)=>console.log(data));
            EOT
            ,
            JSMinifier::get_instance()->minify(
                <<<EOT
                    fetch('http://example.com/movies.json')
                        .then((response) => response.json())
                        .then((data) => console.log(data));

                EOT
            )
        );

        $this->assertEquals(
            <<<EOT
            async function postData(url='',data={}){const response=await fetch(url,{method:'POST',mode:'cors',cache:'no-cache',credentials:'same-origin',headers:{'Content-Type':'application/json'},redirect:'follow',referrerPolicy:'no-referrer',body:JSON.stringify(data)});return response.json();}
            postData('https://example.com/answer',{answer:42}).then((data)=>{console.log(data);});
            EOT
            ,
            JSMinifier::get_instance()->minify(
                <<<EOT
                    // Example POST method implementation:
                    async function postData(url = '', data = {}) {
                        // Default options are marked with *
                        const response = await fetch(url, {
                            method: 'POST', // *GET, POST, PUT, DELETE, etc.
                            mode: 'cors', // no-cors, *cors, same-origin
                            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                            credentials: 'same-origin', // include, *same-origin, omit
                            headers: {
                                'Content-Type': 'application/json'
                                // 'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            redirect: 'follow', // manual, *follow, error
                            referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                            body: JSON.stringify(data) // body data type must match "Content-Type" header
                        });
                        return response.json(); // parses JSON response into native JavaScript objects
                    }

                    postData('https://example.com/answer', { answer: 42 })
                        .then((data) => {
                            console.log(data); // JSON data parsed by `data.json()` call
                        });
                EOT
            )
        );

        $this->assertEquals(
            <<<EOT
            const data={username:'example'};fetch('https://example.com/profile',{method:'POST',headers:{'Content-Type':'application/json',},body:JSON.stringify(data),}).then((response)=>response.json()).then((data)=>{console.log('Success:',data);}).catch((error)=>{console.error('Error:',error);});
            EOT
            ,
            JSMinifier::get_instance()->minify(
                <<<EOT
                    const data = { username: 'example' };

                    fetch('https://example.com/profile', {
                        method: 'POST', // or 'PUT'
                        headers: {
                        'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data),
                    })
                        .then((response) => response.json())
                        .then((data) => {
                        console.log('Success:', data);
                        })
                        .catch((error) => {
                        console.error('Error:', error);
                        });
                EOT
            )
        );
    }
}
?>
