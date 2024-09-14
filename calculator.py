def add(num1,num2):
   return num1+num2
def sub(num1,num2):
   return num1-num2
def mul(num1,num2):
   return num1*num2
def div(num1,num2):
   return num1/num2
def rem(num1,num2):
   return num1%num2
num1=float(input("enter 1st number"))
num2=float(input("enter 2nd number"))
print("select one")
print("1.add")
print("2.subtract")
print("3.multiply")
print("4.divide")
print("5.remainder")
choice=(input("enter choice"))
if choice=='1':
   print(f"result is {add(num1,num2)}")
elif choice=='2':
   print(f"result is {sub(num1,num2)}")
elif choice=='3':
     print(f"result is {mul(num1,num2)}")
elif choice=='4':
     print(f"result is {div(num1,num2)}")
elif choice=='5':
     print(f"result is {rem(num1,num2)}")
else:
    print("invalid input")