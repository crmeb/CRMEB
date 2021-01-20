<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Contact;
use OpenApi\Annotations\License;
use OpenApi\Processors\CleanUnmerged;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Tests\OpenApiTestCase;

class CleanUnmergedTest extends OpenApiTestCase
{
    public function testCleanUnmergedProcessor()
    {
        $comment = <<<END
@OA\Info(
    title="Info only has one contact field.",
    version="test",
)
@OA\PathItem(path="/test"),
@OA\License(
    name="MIT",
    @OA\Contact(
        name="Batman"
    )
)

END;
        $analysis = new Analysis($this->parseComment($comment));
        $this->assertCount(4, $analysis->annotations);
        $analysis->process(new MergeIntoOpenApi());
        $this->assertCount(5, $analysis->annotations);
        $before = $analysis->split();
        $this->assertCount(3, $before->merged->annotations, 'Generated @OA\OpenApi, @OA\PathItem and @OA\Info');
        $this->assertCount(2, $before->unmerged->annotations, '@OA\License + @OA\Contact');
        $this->assertCount(0, $analysis->openapi->_unmerged);
        $analysis->validate(); // Validation fails to detect the unmerged annotations.

        // CleanUnmerged should place the unmerged annotions into the swagger->_unmerged array.
        $analysis->process(new CleanUnmerged());
        $between = $analysis->split();
        $this->assertCount(3, $between->merged->annotations, 'Generated @OA\OpenApi, @OA\PathItem and @OA\Info');
        $this->assertCount(2, $between->unmerged->annotations, '@OA\License + @OA\Contact');
        $this->assertCount(2, $analysis->openapi->_unmerged); // 1 would also be oke, Could a'Only the @OA\License'
        $this->assertOpenApiLogEntryContains('Unexpected @OA\License(), expected to be inside @OA\Info in ');
        $this->assertOpenApiLogEntryContains('Unexpected @OA\Contact(), expected to be inside @OA\Info in ');
        $analysis->validate();

        // When a processor places a previously unmerged annotation into the swagger obect.
        $license = $analysis->getAnnotationsOfType(License::class)[0];
        $contact = $analysis->getAnnotationsOfType(Contact::class)[0];
        $analysis->openapi->info->contact = $contact;
        $this->assertCount(1, $license->_unmerged);
        $analysis->process(new CleanUnmerged());
        $this->assertCount(0, $license->_unmerged);
        $after = $analysis->split();
        $this->assertCount(4, $after->merged->annotations, 'Generated @OA\OpenApi, @OA\PathItem, @OA\Info and @OA\Contact');
        $this->assertCount(1, $after->unmerged->annotations, '@OA\License');
        $this->assertCount(1, $analysis->openapi->_unmerged);
        $this->assertOpenApiLogEntryContains('Unexpected @OA\License(), expected to be inside @OA\Info in ');
        $analysis->validate();
    }
}
