<@php /** * This file is part of Vima PHP. * * (c) Vima PHP <https://github.com/lipex-org/vima-core>
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
    */

    namespace {namespace};

    use Vima\Core\Policy\Attributes\MapToPermission;
    use Vima\Core\Policy\Contracts\PolicyInterface;
    use Vima\Core\Policy\DTOs\AccessContext;
    use {resourceFullClass};

    class {class} implements PolicyInterface
    {
    /**
    * Return the fully qualified class name of the resource this policy handles.
    *
    * @return string
    */
    public static function getResource(): string
    {
    return {resourceClass}::class;
    }

    /**
    * Check if user can view the resource.
    * Maps to ability: 'view' or '{resourceVar}.view'
    *
    * @param AccessContext $ctx
    * @param {resourceClass} ${resourceVar}
    * @return bool
    */
    public function canView(AccessContext $ctx, {resourceClass} ${resourceVar}): bool
    {
    // Example: allow if user has 'admin' role or owns the resource
    // return $ctx->is('admin') || $ctx->owns(${resourceVar}, 'user_id');
    return true;
    }

    /**
    * Check if user can edit the resource.
    * Explicitly mapped to 'edit' ability via attribute.
    *
    * @param AccessContext $ctx
    * @param {resourceClass} ${resourceVar}
    * @return bool
    */
    #[MapToPermission('edit')]
    public function customEditMethod(AccessContext $ctx, {resourceClass} ${resourceVar}): bool
    {
    return $ctx->owns(${resourceVar});
    }
    }