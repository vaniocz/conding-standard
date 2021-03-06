<?php
namespace Vanio\CodingStandards\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use Vanio\CodingStandards\Utility\TokenUtility;

class FunctionDeclarationOpeningBracketSniff implements Sniff
{
    public const CODE_WHITESPACE = 'WhiteSpace';
    public const MESSAGE_WHITESPACE = 'Unexpected white space before opening bracket in declaration of %s %s';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $pointer
     */
    public function process(File $file, $pointer): void
    {
        $tokens = $file->getTokens();
        $openingBracketPointer = $tokens[$pointer]['parenthesis_opener'];

        if (
            $tokens[$pointer]['code'] !== T_FUNCTION
            || $openingBracketPointer === null
            || $tokens[$openingBracketPointer - 1]['code'] !== T_WHITESPACE
        ) {
            return;
        }

        if (!$file->getDeclarationName($pointer)) {
            return;
        }

        $data = [
            $this->resolveFunctionTypeLabel($file, $pointer),
            FunctionHelper::getFullyQualifiedName($file, $pointer),
        ];

        if ($file->addFixableError(self::MESSAGE_WHITESPACE, $openingBracketPointer, self::CODE_WHITESPACE, $data)) {
            TokenUtility::replaceWhiteSpaceBefore($file, $openingBracketPointer);
        }
    }

    private function resolveFunctionTypeLabel(File $file, int $pointer): string
    {
        return FunctionHelper::isMethod($file, $pointer) ? 'method' : 'function';
    }
}
