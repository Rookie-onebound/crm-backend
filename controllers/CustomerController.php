<?php
/**
 * 客户 API 控制器
 */
class CustomerController
{
    private Customer $model;

    public function __construct()
    {
        $this->model = new Customer();
    }

    /** GET /api/customers */
    public function index(): void
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'level'  => $_GET['level'] ?? '',
        ];
        $customers = $this->model->getAll($filters);
        Response::json($customers);
    }

    /** GET /api/customers/{id} */
    public function show(int $id): void
    {
        $customer = $this->model->getById($id);
        if (!$customer) {
            Response::error('客户不存在', 404);
            return;
        }
        Response::json($customer);
    }

    /** POST /api/customers */
    public function store(): void
    {
        $data = Request::body();
        $id = $this->model->create($data);
        $customer = $this->model->getById($id);
        Response::json($customer, 201);
    }

    /** PUT /api/customers/{id} */
    public function update(int $id): void
    {
        $customer = $this->model->getById($id);
        if (!$customer) {
            Response::error('客户不存在', 404);
            return;
        }
        $data = Request::body();
        $this->model->update($id, $data);
        $updated = $this->model->getById($id);
        Response::json($updated);
    }

    /** DELETE /api/customers/{id} */
    public function destroy(int $id): void
    {
        $customer = $this->model->getById($id);
        if (!$customer) {
            Response::error('客户不存在', 404);
            return;
        }
        $this->model->delete($id);
        Response::json(['message' => '删除成功']);
    }
}
