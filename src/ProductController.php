<?php

class ProductController
{
    public function __construct(private ProductGateway $gateway)
    {
    }
    public function processRequest(string $method, ?string $id): void
    {

        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method, $id);
        }
    }
    private function processResourceRequest(string $method, string $id): void
    {
        $product = $this->gateway->get($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($product);
                break;

            case "PATCH":
                // this reads the input as json and converts it back to associative array, if its null because no data we get empty array
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    // response code for unprocessible entity
                    http_response_code(422);

                    // echo back the errors
                    echo json_encode(["errors" => $errors]);

                    // then we break
                    break;
                }

                $rows = $this->gateway->update($product, $data);
                echo json_encode([
                    "message" => "Product $id updated",
                    "rows" => $rows
                ]);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);
                echo json_encode([
                    "message" => "Product $id deleted",
                    "rows" => $rows
                ]);
            
            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
        }
    }

    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":

                echo json_encode($this->gateway->getAll());
                break;

            case "POST":

                // this reads the input as json and converts it back to associative array, if its null because no data we get empty array
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    // response code for unprocessible entity
                    http_response_code(422);

                    // echo back the errors
                    echo json_encode(["errors" => $errors]);

                    // then we break
                    break;
                }

                $id = $this->gateway->create($data);
                echo json_encode(["message" => "Product created", "id" => $id]);

                break;

            default:

                // set method not allowed
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];
        if ($is_new && empty($data["name"])) {
            $errors[] = "name is required";
        }

        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "size must be an integer";
            }
        }

        return $errors;
    }
}
