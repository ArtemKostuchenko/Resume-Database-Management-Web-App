<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    public function getPaginationParams(Request $request, array $defaultOrder = ['created_at' => 'DESC']): array
    {
        $q = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'desc');
        $limit = $request->query->getInt('limit', 10);
        $page = max($request->query->getInt('page', 1), 1);
        $offset = ($page - 1) * $limit;

        $orderBy = $defaultOrder;

        if ($sort === 'desc') {
            $orderBy = ['created_at' => 'DESC'];
        } elseif ($sort === 'asc') {
            $orderBy = ['created_at' => 'ASC'];
        }

        return compact('q', 'sort', 'limit', 'page', 'offset', 'orderBy');
    }
}
