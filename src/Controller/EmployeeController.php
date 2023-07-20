<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use App\Repository\EmpAddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Employee;
use App\Entity\EmpAddress;

class EmployeeController extends AbstractController
{
    private $em;
    private $employeeRepository;
    private $employeeAddressRepository;

    public function __construct(EntityManagerInterface $em, EmployeeRepository $employeeRepository, EmpAddressRepository $employeeAddressRepository) 
    {
        $this->em = $em;
        $this->employeeRepository = $employeeRepository;
        $this->employeeAddressRepository = $employeeAddressRepository;
    }

    /**
     * @Route("/employee", name="app_employee")
     */
    public function index(): Response
    {
        $employees = $this->employeeRepository->findAll();

        $data['employees'] = $employees;
        $data['title'] = 'Employees';
        return $this->render('employees.html.twig', $data);
    }


    /**
     * @Route("/save-employee", name="save-employee", methods={"POST"})
     */
    public function save_employee(Request $request): JsonResponse
    {
        // print_r($request->request->all());

        $employee_id = $request->request->get('employee_id');
        if($employee_id == '')
        $employee = new Employee();
        else
        $employee = $this->employeeRepository->findOneBy(['id' => $employee_id]);
        
        // echo $request->request->get('phone');die;
        $employee->setName($request->request->get('name'));
        $employee->setPhone($request->request->get('phone'));
        $employee->setCreatedAt(new \DateTimeImmutable());
        $employee->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($employee);
        $this->em->flush();

        return $this->json([
            'message' => 'Employee Saved',
        ]);
    }

     /**
     * @Route("/get-all-employees", name="ajax-get-all-employees")
     */
    public function ajax_get_all_employees(Request $request): JsonResponse
    {
        $employees = $this->employeeRepository->findAll();
        
        $data = [];
        
        foreach ($employees as $employee) {
            $row = [];
            
            // print_r($employee->getName());die;
            $row[] = $employee->getName();
            $row[] = $employee->getPhone();
            $row[] = '<button class="btn btn-primary edit-employee" data-id="'.$employee->getId().'">Edit</button>
                        <a href="'.$this->generateUrl('employee-addresses', array('id' => $employee->getId())).'" class="btn btn-info">Addresses</a>
                        <button class="btn btn-danger delete-employee" data-id="'.$employee->getId().'">Delete</button>';

            $data[] = $row;
        }        

        return $this->json([
            'data' => $data,
        ]);
    }

     /**
     * @Route("/get-employee-data", name="ajax-get-employee-data")
     */
    public function ajax_get_employee_data(Request $request): JsonResponse
    {
        $employee_id = $request->query->get('employee_id');
        $employee = $this->employeeRepository->findOneBy(['id' => $employee_id]);
        
        $data = [
            'id' => $employee->getId(),
            'name' => $employee->getName(),
            'phone' => $employee->getPhone(),
        ]; 

        return $this->json([
            'data' => $data,
        ]);
    }

     /**
     * @Route("/remove-employee", name="ajax-remove-employee")
     */
    public function ajax_remove_employee_data(Request $request): JsonResponse
    {
        $employee_id = $request->query->get('employee_id');
        $employee = $this->employeeRepository->findOneBy(['id' => $employee_id]);

        $this->em->remove($employee);
        $this->em->flush();

        return $this->json([
            'message' => 'Employee Removed',
        ]);
    }

    /**
     * @Route("/employee-addresses/{id}", name="employee-addresses")
     */
    public function employeee_address($id): Response
    {
        $employee = $this->employeeRepository->findOneBy(['id' => $id]);

        $data['title'] = $employee->getName().' - Employee Address';

        return $this->render('employee-addresses.html.twig', $data);
    }

    /**
     * @Route("/save-employee-address/{employee_id}", name="save-employee-address", methods={"POST"})
     */
    public function save_employee_address(Request $request, $employee_id): JsonResponse
    {
        // print_r($request->request->get('address'));
        $employee = $this->employeeRepository->findOneBy(['id' => $employee_id]);

        $address_id = $request->request->get('address_id');
        if($address_id == '')
        $employee_address = new EmpAddress();
        else
        $employee_address = $this->employeeAddressRepository->findOneBy(['id' => $address_id]);
        
        $employee_address->setEmployeeId($employee);
        $employee_address->setAddress($request->request->get('address'));
        $employee_address->setCity($request->request->get('city'));
        $employee_address->setState($request->request->get('state'));
        $employee_address->setCountry($request->request->get('country'));
        $employee_address->setCreatedAt(new \DateTimeImmutable());
        $employee_address->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($employee_address);
        $this->em->flush();

        return $this->json([
            'message' => 'Employee Address Saved',
        ]);
    }

    /**
     * @Route("/get-all-employee-addresses/{employee_id}", name="ajax-get-all-employee-addresses")
     */
    public function ajax_get_all_employee_addresses(Request $request, $employee_id): JsonResponse
    {
        $employee = $this->employeeRepository->findOneBy(['id' => $employee_id]);
        $employee_addresses = $employee->getEmpAddresses();
        // $employee_addresses = $this->employeeAddressRepository->findBy(['employee_id' => $employee_id]);
        
        $data = [];
        
        foreach ($employee_addresses as $address) {
            $row = [];
            
            $row[] = $address->getAddress();
            $row[] = $address->getCity();
            $row[] = $address->getState();
            $row[] = $address->getCountry();
            $row[] = '<button class="btn btn-primary edit-address" data-id="'.$address->getId().'">Edit</button>
                        <button class="btn btn-danger delete-address" data-id="'.$address->getId().'">Delete</button>';

            $data[] = $row;
        }        

        return $this->json([
            'data' => $data,
        ]);
    }

    /**
     * @Route("/get-address-data", name="ajax-get-address-data")
     */
    public function ajax_get_address_data(Request $request): JsonResponse
    {
        $address_id = $request->query->get('address_id');
        $address = $this->employeeAddressRepository->findOneBy(['id' => $address_id]);
        
        $data = [
            'id' => $address->getId(),
            'address' => $address->getAddress(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'country' => $address->getCountry(),
        ]; 

        return $this->json([
            'data' => $data,
        ]);
    }

    /**
     * @Route("/remove-address", name="ajax-remove-address")
     */
    public function ajax_remove_address_data(Request $request): JsonResponse
    {
        $address_id = $request->query->get('address_id');
        $address = $this->employeeAddressRepository->findOneBy(['id' => $address_id]);

        $this->em->remove($address);
        $this->em->flush();

        return $this->json([
            'message' => 'Address Removed',
        ]);
    }
}
