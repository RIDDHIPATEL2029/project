try:
    a=int(input("enter no:"))
    b=int(input("enter no:"))
    c=a/b
    print("result:",c)
except ZeroDivisionError:
    print("values of a and b should be numeric")
else:
    print("no exception")
