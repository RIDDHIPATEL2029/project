try:
    x=int(input("enter no:"))
    y=int(input("enter no:"))
    z=x/y
except ArithmaticError as e:
    print("cannot divide a no by zero",e)
finally:
    print("finally block")
