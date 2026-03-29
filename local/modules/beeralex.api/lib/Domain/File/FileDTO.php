<?

declare(strict_types=1);

namespace Beeralex\Api\Domain\File;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $src
 * @property int $size
 */
class FileDTO extends Resource
{
    public static function make(array $file): static
    {
        return new static([
            'src' => $file['SRC'] ?? '',
            'size' => (int)($file['FILE_SIZE'] ?? 0),
        ]);
    }
}
