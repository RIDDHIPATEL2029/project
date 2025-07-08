a=int(input("enter no 1:"))
b=int(input("enter no.2:"))
try:
    c=a/b
    print("quntinate:",c)
except ZeroDivisionError:
    print("division by zero")
