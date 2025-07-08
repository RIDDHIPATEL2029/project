a = int(input("Enter the marks of first subject: "))
b = int(input("Enter the marks of second subject: "))
c = int(input("Enter the marks of third subject: "))
total = a+b+c
avg = total/3
print("Total marks: ",total)
print("Average marks: ",avg)
print("Enter Marks Obtained in 5 Subjects: ")
if avg>=91 and avg<=100:
    print("Your Grade is A")
elif avg>=81 and avg<91:
    print("Your Grade is B")
elif avg>=51 and avg<61:
    print("Your Grade is C")
elif avg>=33 and avg<41:
    print("Your Grade is D")
elif avg>=21 and avg<33:
    print("Your are fail")
else:
    print("Invalid Input!")
