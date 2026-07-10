<?php
/**
 * 报价 API 控制器
 */
class QuoteController
{
    private Quote $model;

    public function __construct()
    {
        $this->model = new Quote();
    }

    /** GET /api/quotes/customer/{customerId} */
    public function byCustomer(int $customerId): void
    {
        $quotes = $this->model->getByCustomer($customerId);
        Response::json($quotes);
    }

    /** GET /api/quotes/{id} */
    public function show(int $id): void
    {
        $quote = $this->model->getById($id);
        if (!$quote) {
            Response::error('报价不存在', 404);
            return;
        }
        Response::json($quote);
    }

    /** POST /api/quotes */
    public function store(): void
    {
        $data = Request::body();
        $id = $this->model->create($data);
        $quote = $this->model->getById($id);
        Response::json($quote, 201);
    }

    /** PUT /api/quotes/{id} */
    public function update(int $id): void
    {
        $quote = $this->model->getById($id);
        if (!$quote) {
            Response::error('报价不存在', 404);
            return;
        }
        $data = Request::body();
        $this->model->update($id, $data);
        $updated = $this->model->getById($id);
        Response::json($updated);
    }

    /** DELETE /api/quotes/{id} */
    public function destroy(int $id): void
    {
        $quote = $this->model->getById($id);
        if (!$quote) {
            Response::error('报价不存在', 404);
            return;
        }
        $this->model->delete($id);
        Response::json(['message' => '删除成功']);
    }
}
