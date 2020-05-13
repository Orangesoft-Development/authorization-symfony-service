<?php

namespace App\Tests\Validator;

use App\Storage\StreamedFile;
use App\Validator\StreamFile;
use App\Validator\StreamFileValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StreamFileValidatorTest extends TestCase
{
    /**
     * @var ExecutionContext
     */
    private $context;

    /**
     * @var StreamFileValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getContext();

        $this->validator = new StreamFileValidator();
        $this->validator->initialize($this->context);
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new StreamFile());

        $this->assertNoViolation();
    }

    public function testValidate(): void
    {
        $streamedFile = $this->getStreamedFile(5, 'image/jpg');

        $constraint = new StreamFile([
            'maxSize' => 5,
            'mimeTypes' => ['image/png', 'image/jpg'],
        ]);

        $this->validator->validate($streamedFile, $constraint);

        $this->assertNoViolation();
    }

    public function testValidMinSize(): void
    {
        $streamedFile = $this->getStreamedFile(1);

        $this->assertTrue($this->validator->validateMinSize($streamedFile, new StreamFile()));
    }

    public function testInvalidMinSize(): void
    {
        $streamedFile = $this->getStreamedFile(0);

        $this->assertFalse($this->validator->validateMinSize($streamedFile, new StreamFile()));
    }

    public function testMaxSizeExceeded(): void
    {
        $streamedFile = $this->getStreamedFile(10);

        $constraint = new StreamFile([
            'maxSize' => 5,
        ]);

        $this->assertFalse($this->validator->validateMaxSize($streamedFile, $constraint));
    }

    public function testMaxSizeNotExceeded(): void
    {
        $streamedFile = $this->getStreamedFile(5);

        $constraint = new StreamFile([
            'maxSize' => 10,
        ]);

        $this->assertTrue($this->validator->validateMaxSize($streamedFile, $constraint));
    }

    public function testValidMimeType(): void
    {
        $streamedFile = $this->getStreamedFile(null, 'image/jpg');

        $constraint = new StreamFile([
            'mimeTypes' => ['image/png', 'image/jpg'],
        ]);

        $this->validator->validateMimeType($streamedFile, $constraint);

        $this->assertTrue($this->validator->validateMimeType($streamedFile, $constraint));
    }

    public function testInvalidMimeType(): void
    {
        $streamedFile = $this->getStreamedFile(null, 'application/pdf');

        $constraint = new StreamFile([
            'mimeTypes' => ['image/png', 'image/jpg'],
        ]);

        $this->validator->validateMimeType($streamedFile, $constraint);

        $this->assertFalse($this->validator->validateMimeType($streamedFile, $constraint));
    }

    private function assertNoViolation(): void
    {
        $violationsCount = count($this->context->getViolations());
        $message = sprintf('0 violation expected. Got %u.', $violationsCount);

        $this->assertSame(0, $violationsCount, $message);
    }

    /**
     * @return ExecutionContext
     */
    private function getContext(): ExecutionContext
    {
        /** @var TranslatorInterface|MockObject $translator */
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator
            ->expects($this->any())
            ->method('trans')
            ->willReturn('Error')
        ;

        /** @var ValidatorInterface|MockObject $validator */
        $validator = $this->createMock(ValidatorInterface::class);
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $context = new ExecutionContext($validator, 'root', $translator);
        $context->setGroup('group');
        $context->setNode('InvalidValue', null, null, 'property.path');
        $context->setConstraint(new NotNull());

        $validator
            ->expects($this->any())
            ->method('inContext')
            ->with($context)
            ->willReturn($contextualValidator)
        ;

        return $context;
    }

    /**
     * @param string|null $size
     * @param string|null $mimeType
     *
     * @return StreamedFile|MockObject
     */
    private function getStreamedFile(?string $size = null, ?string $mimeType = null): MockObject
    {
        /** @var StreamedFile|MockObject $streamedFile */
        $streamedFile = $this->createMock(StreamedFile::class);

        if (null !== $size) {
            $streamedFile
                ->expects($this->any())
                ->method('getSize')
                ->willReturn($size)
            ;
        }

        if (null !== $mimeType) {
            $streamedFile
                ->expects($this->any())
                ->method('getMimeType')
                ->willReturn($mimeType)
            ;
        }

        return $streamedFile;
    }
}
