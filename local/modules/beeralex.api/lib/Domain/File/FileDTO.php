<?

declare(strict_types=1);

namespace Beeralex\Api\Domain\File;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $src
 * @property int $size
 * @property string $name
 * @property string $type
 * @property string $extension
 */
class FileDTO extends Resource
{
    public static function make(array $file): static
    {
        $ext = strtolower(pathinfo((string)$file['SRC'], PATHINFO_EXTENSION));
        $type = (string)($file['CONTENT_TYPE'] ?? '');
        return new static([
            'src' => $file['SRC'] ?? '',
            'size' => (int)($file['FILE_SIZE'] ?? 0),
            'name' => (string)($file['ORIGINAL_NAME'] ?? basename((string)$file['SRC'])),
            'type' => $type,
            'extension' => $ext
        ]);
    }
}
